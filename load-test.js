/**
 * k6 Load Test Script for Search Engine Service
 * 
 * Installation:
 * - macOS: brew install k6
 * - Linux: sudo apt-get install k6
 * - Windows: choco install k6
 * 
 * Usage:
 * k6 run load-test.js
 * 
 * With custom options:
 * k6 run --vus 50 --duration 30s load-test.js
 */

import http from 'k6/http';
import { check, sleep } from 'k6';
import { Rate, Trend, Counter } from 'k6/metrics';

// Custom metrics
const errorRate = new Rate('errors');
const searchDuration = new Trend('search_duration');
const cacheHits = new Counter('cache_hits');
const cacheMisses = new Counter('cache_misses');

// Test configuration
export const options = {
  stages: [
    { duration: '30s', target: 20 },   // Warm-up: ramp to 20 users
    { duration: '1m', target: 50 },    // Normal load: 50 users
    { duration: '30s', target: 100 },  // Peak load: 100 users
    { duration: '1m', target: 100 },   // Sustained peak
    { duration: '30s', target: 200 },  // Spike test: 200 users
    { duration: '30s', target: 0 },    // Ramp-down
  ],
  thresholds: {
    'http_req_duration': ['p(95)<1000', 'p(99)<2000'], // 95% < 1s, 99% < 2s
    'http_req_failed': ['rate<0.05'],                   // Error rate < 5%
    'errors': ['rate<0.05'],
    'search_duration': ['p(95)<800'],
  },
};

// Test data
const keywords = [
  'docker', 'php', 'symfony', 'redis', 'mysql',
  'kubernetes', 'api', 'microservices', 'testing',
  'architecture', 'design patterns', 'clean code'
];

const types = ['video', 'article'];
const sortOptions = ['score', 'date'];
const perPageOptions = [10, 20, 50];

// Base URL
const BASE_URL = __ENV.BASE_URL || 'http://localhost:8080';

export default function () {
  // Random test data
  const keyword = keywords[Math.floor(Math.random() * keywords.length)];
  const type = types[Math.floor(Math.random() * types.length)];
  const sortBy = sortOptions[Math.floor(Math.random() * sortOptions.length)];
  const page = Math.floor(Math.random() * 5) + 1;
  const perPage = perPageOptions[Math.floor(Math.random() * perPageOptions.length)];

  // Test 1: Search API
  const searchStart = Date.now();
  const searchRes = http.get(
    `${BASE_URL}/api/search?keyword=${keyword}&type=${type}&sortBy=${sortBy}&page=${page}&perPage=${perPage}`,
    {
      tags: { name: 'SearchAPI' },
    }
  );
  const searchEnd = Date.now();
  
  // Record metrics
  searchDuration.add(searchEnd - searchStart);
  
  // Checks
  const searchSuccess = check(searchRes, {
    'search: status is 200': (r) => r.status === 200,
    'search: response time < 1000ms': (r) => r.timings.duration < 1000,
    'search: has success field': (r) => {
      try {
        const body = JSON.parse(r.body);
        return body.success === true;
      } catch (e) {
        return false;
      }
    },
    'search: has data array': (r) => {
      try {
        const body = JSON.parse(r.body);
        return Array.isArray(body.data);
      } catch (e) {
        return false;
      }
    },
    'search: has pagination': (r) => {
      try {
        const body = JSON.parse(r.body);
        return body.pagination && 
               typeof body.pagination.total === 'number' &&
               typeof body.pagination.page === 'number';
      } catch (e) {
        return false;
      }
    },
  });
  
  if (!searchSuccess) {
    errorRate.add(1);
    console.error(`Search failed: ${searchRes.status} - ${searchRes.body}`);
  } else {
    errorRate.add(0);
    
    // Check if response was cached (custom header or fast response)
    if (searchRes.timings.duration < 100) {
      cacheHits.add(1);
    } else {
      cacheMisses.add(1);
    }
  }

  sleep(1); // Think time between requests

  // Test 2: Dashboard (10% of users)
  if (Math.random() < 0.1) {
    const dashboardRes = http.get(`${BASE_URL}/`, {
      tags: { name: 'Dashboard' },
    });
    
    check(dashboardRes, {
      'dashboard: status is 200': (r) => r.status === 200,
      'dashboard: response time < 2000ms': (r) => r.timings.duration < 2000,
    });
    
    sleep(2);
  }

  // Test 3: API Documentation (5% of users)
  if (Math.random() < 0.05) {
    const apiDocRes = http.get(`${BASE_URL}/api/doc`, {
      tags: { name: 'APIDoc' },
    });
    
    check(apiDocRes, {
      'api-doc: status is 200': (r) => r.status === 200,
    });
    
    sleep(1);
  }
}

// Setup function (runs once at the beginning)
export function setup() {
  console.log('🚀 Starting load test...');
  console.log(`📍 Target: ${BASE_URL}`);
  console.log('⏱️  Duration: ~4.5 minutes');
  console.log('👥 Max users: 200');
  
  // Health check
  const healthRes = http.get(`${BASE_URL}/api/search?keyword=test`);
  if (healthRes.status !== 200) {
    throw new Error(`Service is not healthy: ${healthRes.status}`);
  }
  
  console.log('✅ Service is healthy, starting test...\n');
  
  return { startTime: Date.now() };
}

// Teardown function (runs once at the end)
export function teardown(data) {
  const duration = (Date.now() - data.startTime) / 1000;
  console.log(`\n✅ Load test completed in ${duration.toFixed(2)} seconds`);
}

// Handle summary
export function handleSummary(data) {
  // Check if metrics exist
  if (!data.metrics || !data.metrics.cache_hits || !data.metrics.cache_misses) {
    console.log('\n📊 Test Summary:');
    console.log('================');
    console.log(`Total Requests: ${data.metrics.http_reqs ? data.metrics.http_reqs.values.count : 0}`);
    console.log(`Failed Requests: ${data.metrics.http_req_failed ? (data.metrics.http_req_failed.values.rate * 100).toFixed(2) : 0}%`);
    
    return {
      'stdout': JSON.stringify(data, null, 2),
    };
  }

  const cacheHitRate = data.metrics.cache_hits.values.count / 
                       (data.metrics.cache_hits.values.count + data.metrics.cache_misses.values.count) * 100;
  
  console.log('\n📊 Test Summary:');
  console.log('================');
  console.log(`Total Requests: ${data.metrics.http_reqs.values.count}`);
  console.log(`Failed Requests: ${(data.metrics.http_req_failed.values.rate * 100).toFixed(2)}%`);
  console.log(`Avg Response Time: ${data.metrics.http_req_duration.values.avg.toFixed(2)}ms`);
  console.log(`95th Percentile: ${data.metrics.http_req_duration.values['p(95)'].toFixed(2)}ms`);
  console.log(`99th Percentile: ${data.metrics.http_req_duration.values['p(99)'].toFixed(2)}ms`);
  console.log(`Requests/sec: ${data.metrics.http_reqs.values.rate.toFixed(2)}`);
  console.log(`Cache Hit Rate: ${cacheHitRate.toFixed(2)}%`);
  
  return {
    'stdout': JSON.stringify(data, null, 2),
    'summary.json': JSON.stringify(data, null, 2),
  };
}
