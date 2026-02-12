package main

import (
	"encoding/json"
	"flag"
	"fmt"
	"io"
	"math"
	"math/rand"
	"net/http"
	"os"
	"sort"
	"sync"
	"sync/atomic"
	"time"
)

// Configuration
var (
	baseURL     = flag.String("url", "http://localhost:8080", "Base URL of the service")
	requests    = flag.Int("requests", 1000, "Total number of requests")
	concurrent  = flag.Int("concurrent", 10, "Number of concurrent workers")
	duration    = flag.Duration("duration", 0, "Test duration (overrides requests)")
	scenario    = flag.String("scenario", "basic", "Test scenario: basic|stress|spike")
	outputFile  = flag.String("output", "", "Output file for results (JSON)")
	showVersion = flag.Bool("version", false, "Show version")
)

// Test data
var (
	keywords = []string{
		"docker", "php", "symfony", "redis", "mysql",
		"kubernetes", "api", "microservices", "testing",
		"architecture", "design patterns", "clean code",
	}
	types         = []string{"video", "article"}
	sortOptions   = []string{"score", "date"}
	perPageOptions = []int{10, 20, 50}
)

// Metrics
type Metrics struct {
	TotalRequests   int64
	SuccessCount    int64
	FailureCount    int64
	ResponseTimes   []float64
	CacheHits       int64
	CacheMisses     int64
	StartTime       time.Time
	EndTime         time.Time
	mu              sync.Mutex
}

type APIResponse struct {
	Success    bool                   `json:"success"`
	Data       []interface{}          `json:"data"`
	Pagination map[string]interface{} `json:"pagination"`
}

func main() {
	flag.Parse()

	if *showVersion {
		fmt.Println("Load Test Tool v1.0.0")
		return
	}

	fmt.Println("🚀 Search Engine Load Test")
	fmt.Println("=" + string(make([]byte, 50)))
	fmt.Printf("Base URL:     %s\n", *baseURL)
	fmt.Printf("Scenario:     %s\n", *scenario)
	fmt.Printf("Requests:     %d\n", *requests)
	fmt.Printf("Concurrent:   %d\n", *concurrent)
	if *duration > 0 {
		fmt.Printf("Duration:     %s\n", *duration)
	}
	fmt.Println()

	// Health check
	if !healthCheck(*baseURL) {
		fmt.Println("❌ Service is not healthy")
		os.Exit(1)
	}

	// Initialize metrics
	metrics := &Metrics{
		ResponseTimes: make([]float64, 0),
		StartTime:     time.Now(),
	}

	// Run test based on scenario
	switch *scenario {
	case "stress":
		runStressTest(*baseURL, metrics)
	case "spike":
		runSpikeTest(*baseURL, metrics)
	default:
		runBasicTest(*baseURL, *requests, *concurrent, metrics)
	}

	metrics.EndTime = time.Now()

	// Display results
	displayResults(metrics)

	// Save results if output file specified
	if *outputFile != "" {
		saveResults(*outputFile, metrics)
		fmt.Printf("\n✅ Results saved to: %s\n", *outputFile)
	}
}

func healthCheck(baseURL string) bool {
	fmt.Println("🔍 Health Check...")
	
	resp, err := http.Get(baseURL + "/api/search?keyword=test")
	if err != nil {
		fmt.Printf("❌ Error: %v\n", err)
		return false
	}
	defer resp.Body.Close()

	if resp.StatusCode == 200 {
		fmt.Println("✅ Service is healthy\n")
		return true
	}

	fmt.Printf("❌ Service returned status: %d\n", resp.StatusCode)
	return false
}

func runBasicTest(baseURL string, totalRequests, concurrent int, metrics *Metrics) {
	fmt.Println("📊 Running Basic Load Test...")
	
	jobs := make(chan int, totalRequests)
	var wg sync.WaitGroup

	// Start workers
	for i := 0; i < concurrent; i++ {
		wg.Add(1)
		go worker(baseURL, jobs, metrics, &wg)
	}

	// Send jobs
	for i := 0; i < totalRequests; i++ {
		jobs <- i
		if (i+1)%100 == 0 {
			fmt.Printf("\rProgress: %d/%d", i+1, totalRequests)
		}
	}
	close(jobs)

	wg.Wait()
	fmt.Printf("\rProgress: %d/%d ✅\n", totalRequests, totalRequests)
}

func runStressTest(baseURL string, metrics *Metrics) {
	fmt.Println("📊 Running Stress Test...")
	
	stages := []struct {
		users    int
		duration time.Duration
		label    string
	}{
		{10, 30 * time.Second, "Warm-up (10 users)"},
		{50, 60 * time.Second, "Normal load (50 users)"},
		{100, 60 * time.Second, "High load (100 users)"},
		{200, 30 * time.Second, "Peak load (200 users)"},
	}

	for _, stage := range stages {
		fmt.Printf("\n%s\n", stage.label)
		runStage(baseURL, stage.users, stage.duration, metrics)
	}
}

func runSpikeTest(baseURL string, metrics *Metrics) {
	fmt.Println("📊 Running Spike Test...")
	
	fmt.Println("\nNormal load (20 users, 30s)")
	runStage(baseURL, 20, 30*time.Second, metrics)
	
	fmt.Println("\n⚡ SPIKE! (200 users, 10s)")
	runStage(baseURL, 200, 10*time.Second, metrics)
	
	fmt.Println("\nRecovery (20 users, 30s)")
	runStage(baseURL, 20, 30*time.Second, metrics)
}

func runStage(baseURL string, users int, duration time.Duration, metrics *Metrics) {
	endTime := time.Now().Add(duration)
	jobs := make(chan int, users*100)
	var wg sync.WaitGroup

	// Start workers
	for i := 0; i < users; i++ {
		wg.Add(1)
		go worker(baseURL, jobs, metrics, &wg)
	}

	// Send jobs until duration expires
	jobCount := 0
	for time.Now().Before(endTime) {
		for i := 0; i < users; i++ {
			jobs <- jobCount
			jobCount++
		}
		time.Sleep(1 * time.Second)
	}
	close(jobs)

	wg.Wait()
	fmt.Printf("Completed %d requests\n", jobCount)
}

func worker(baseURL string, jobs <-chan int, metrics *Metrics, wg *sync.WaitGroup) {
	defer wg.Done()

	client := &http.Client{
		Timeout: 10 * time.Second,
	}

	for range jobs {
		url := generateRandomURL(baseURL)
		startTime := time.Now()

		resp, err := client.Get(url)
		responseTime := time.Since(startTime).Seconds() * 1000 // Convert to ms

		atomic.AddInt64(&metrics.TotalRequests, 1)

		if err != nil {
			atomic.AddInt64(&metrics.FailureCount, 1)
			metrics.mu.Lock()
			metrics.ResponseTimes = append(metrics.ResponseTimes, responseTime)
			metrics.mu.Unlock()
			continue
		}

		body, _ := io.ReadAll(resp.Body)
		resp.Body.Close()

		if resp.StatusCode == 200 {
			atomic.AddInt64(&metrics.SuccessCount, 1)

			// Check if cached (fast response)
			if responseTime < 100 {
				atomic.AddInt64(&metrics.CacheHits, 1)
			} else {
				atomic.AddInt64(&metrics.CacheMisses, 1)
			}

			// Validate response
			var apiResp APIResponse
			if err := json.Unmarshal(body, &apiResp); err == nil {
				if !apiResp.Success {
					atomic.AddInt64(&metrics.FailureCount, 1)
				}
			}
		} else {
			atomic.AddInt64(&metrics.FailureCount, 1)
		}

		metrics.mu.Lock()
		metrics.ResponseTimes = append(metrics.ResponseTimes, responseTime)
		metrics.mu.Unlock()
	}
}

func generateRandomURL(baseURL string) string {
	keyword := keywords[rand.Intn(len(keywords))]
	contentType := types[rand.Intn(len(types))]
	sortBy := sortOptions[rand.Intn(len(sortOptions))]
	page := rand.Intn(5) + 1
	perPage := perPageOptions[rand.Intn(len(perPageOptions))]

	return fmt.Sprintf(
		"%s/api/search?keyword=%s&type=%s&sortBy=%s&page=%d&perPage=%d",
		baseURL, keyword, contentType, sortBy, page, perPage,
	)
}

func displayResults(metrics *Metrics) {
	fmt.Println("\n" + string(make([]byte, 50)))
	fmt.Println("📊 Test Results")
	fmt.Println(string(make([]byte, 50)))

	totalTime := metrics.EndTime.Sub(metrics.StartTime).Seconds()
	successRate := float64(metrics.SuccessCount) / float64(metrics.TotalRequests) * 100
	requestsPerSecond := float64(metrics.TotalRequests) / totalTime

	// Calculate response time statistics
	sort.Float64s(metrics.ResponseTimes)
	avgResponseTime := average(metrics.ResponseTimes)
	minResponseTime := metrics.ResponseTimes[0]
	maxResponseTime := metrics.ResponseTimes[len(metrics.ResponseTimes)-1]
	p50 := percentile(metrics.ResponseTimes, 50)
	p95 := percentile(metrics.ResponseTimes, 95)
	p99 := percentile(metrics.ResponseTimes, 99)

	totalCacheRequests := metrics.CacheHits + metrics.CacheMisses
	cacheHitRate := float64(metrics.CacheHits) / float64(totalCacheRequests) * 100

	fmt.Printf("Total Requests:        %d\n", metrics.TotalRequests)
	fmt.Printf("Successful:            %d\n", metrics.SuccessCount)
	fmt.Printf("Failed:                %d\n", metrics.FailureCount)
	fmt.Printf("Success Rate:          %.2f%%\n", successRate)
	fmt.Printf("Total Time:            %.2fs\n", totalTime)
	fmt.Printf("Requests/sec:          %.2f\n", requestsPerSecond)
	fmt.Println()
	fmt.Printf("Avg Response Time:     %.2fms\n", avgResponseTime)
	fmt.Printf("Min Response Time:     %.2fms\n", minResponseTime)
	fmt.Printf("Max Response Time:     %.2fms\n", maxResponseTime)
	fmt.Printf("50th Percentile:       %.2fms\n", p50)
	fmt.Printf("95th Percentile:       %.2fms\n", p95)
	fmt.Printf("99th Percentile:       %.2fms\n", p99)
	fmt.Println()
	fmt.Printf("Cache Hits:            %d\n", metrics.CacheHits)
	fmt.Printf("Cache Misses:          %d\n", metrics.CacheMisses)
	fmt.Printf("Cache Hit Rate:        %.2f%%\n", cacheHitRate)

	// Performance assessment
	fmt.Println("\n🎯 Performance Assessment")
	fmt.Println(string(make([]byte, 50)))

	if successRate < 95 {
		fmt.Printf("⚠️  High error rate: %d failures\n", metrics.FailureCount)
	}
	if p95 > 1000 {
		fmt.Println("⚠️  95th percentile > 1000ms - Consider optimization")
	}
	if cacheHitRate < 50 {
		fmt.Printf("⚠️  Low cache hit rate: %.2f%%\n", cacheHitRate)
	}
	if successRate >= 95 && p95 <= 1000 && cacheHitRate >= 50 {
		fmt.Println("✅ All metrics look good!")
	}
}

func saveResults(filename string, metrics *Metrics) {
	totalTime := metrics.EndTime.Sub(metrics.StartTime).Seconds()
	successRate := float64(metrics.SuccessCount) / float64(metrics.TotalRequests) * 100
	requestsPerSecond := float64(metrics.TotalRequests) / totalTime

	sort.Float64s(metrics.ResponseTimes)
	avgResponseTime := average(metrics.ResponseTimes)

	results := map[string]interface{}{
		"timestamp": time.Now().Format(time.RFC3339),
		"summary": map[string]interface{}{
			"total_requests":       metrics.TotalRequests,
			"successful":           metrics.SuccessCount,
			"failed":               metrics.FailureCount,
			"success_rate":         math.Round(successRate*100) / 100,
			"total_time":           math.Round(totalTime*100) / 100,
			"requests_per_second":  math.Round(requestsPerSecond*100) / 100,
		},
		"response_times": map[string]interface{}{
			"avg": math.Round(avgResponseTime*100) / 100,
			"min": math.Round(metrics.ResponseTimes[0]*100) / 100,
			"max": math.Round(metrics.ResponseTimes[len(metrics.ResponseTimes)-1]*100) / 100,
			"p50": math.Round(percentile(metrics.ResponseTimes, 50)*100) / 100,
			"p95": math.Round(percentile(metrics.ResponseTimes, 95)*100) / 100,
			"p99": math.Round(percentile(metrics.ResponseTimes, 99)*100) / 100,
		},
		"cache": map[string]interface{}{
			"hits":     metrics.CacheHits,
			"misses":   metrics.CacheMisses,
			"hit_rate": math.Round(float64(metrics.CacheHits)/float64(metrics.CacheHits+metrics.CacheMisses)*10000) / 100,
		},
	}

	data, _ := json.MarshalIndent(results, "", "  ")
	os.WriteFile(filename, data, 0644)
}

func average(values []float64) float64 {
	sum := 0.0
	for _, v := range values {
		sum += v
	}
	return sum / float64(len(values))
}

func percentile(values []float64, p float64) float64 {
	index := (float64(len(values)) - 1) * (p / 100)
	lower := int(math.Floor(index))
	upper := int(math.Ceil(index))
	weight := index - float64(lower)

	return values[lower]*(1-weight) + values[upper]*weight
}
