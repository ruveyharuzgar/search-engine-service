#!/bin/bash

###############################################################################
# Load Test Script for Search Engine Service
# Uses Apache Bench (ab) - Simple and fast
#
# Usage:
#   ./load-test.sh
#   ./load-test.sh quick    # Quick test (100 requests)
#   ./load-test.sh stress   # Stress test (10000 requests)
###############################################################################

set -e

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Configuration
BASE_URL="${BASE_URL:-http://localhost:8080}"
RESULTS_DIR="load-test-results"

# Create results directory
mkdir -p "$RESULTS_DIR"

# Functions
print_header() {
    echo -e "${BLUE}========================================${NC}"
    echo -e "${BLUE}$1${NC}"
    echo -e "${BLUE}========================================${NC}"
}

print_success() {
    echo -e "${GREEN}✅ $1${NC}"
}

print_error() {
    echo -e "${RED}❌ $1${NC}"
}

print_info() {
    echo -e "${YELLOW}ℹ️  $1${NC}"
}

# Check if service is running
check_service() {
    print_header "Checking Service Health"
    
    if curl -s -f "$BASE_URL/api/search?keyword=test" > /dev/null; then
        print_success "Service is running at $BASE_URL"
    else
        print_error "Service is not responding at $BASE_URL"
        exit 1
    fi
    echo ""
}

# Run Apache Bench test
run_ab_test() {
    local name=$1
    local url=$2
    local requests=$3
    local concurrency=$4
    local output_file="$RESULTS_DIR/${name}_$(date +%Y%m%d_%H%M%S).txt"
    
    print_info "Running: $name"
    print_info "URL: $url"
    print_info "Requests: $requests, Concurrency: $concurrency"
    
    ab -n "$requests" -c "$concurrency" -g "$output_file.tsv" "$url" > "$output_file" 2>&1
    
    # Extract key metrics
    local rps=$(grep "Requests per second" "$output_file" | awk '{print $4}')
    local mean_time=$(grep "Time per request" "$output_file" | head -1 | awk '{print $4}')
    local failed=$(grep "Failed requests" "$output_file" | awk '{print $3}')
    
    echo ""
    print_success "Completed: $name"
    echo "  📊 Requests/sec: $rps"
    echo "  ⏱️  Mean time: ${mean_time}ms"
    echo "  ❌ Failed: $failed"
    echo "  📄 Full report: $output_file"
    echo ""
}

# Test scenarios
test_basic_search() {
    print_header "Test 1: Basic Search"
    run_ab_test "basic_search" \
        "$BASE_URL/api/search?keyword=docker" \
        1000 10
}

test_filtered_search() {
    print_header "Test 2: Filtered Search (Videos)"
    run_ab_test "filtered_search" \
        "$BASE_URL/api/search?keyword=php&type=video&sortBy=score" \
        1000 10
}

test_paginated_search() {
    print_header "Test 3: Paginated Search"
    run_ab_test "paginated_search" \
        "$BASE_URL/api/search?keyword=symfony&page=2&perPage=20" \
        1000 10
}

test_high_concurrency() {
    print_header "Test 4: High Concurrency"
    run_ab_test "high_concurrency" \
        "$BASE_URL/api/search?keyword=redis" \
        5000 100
}

test_stress() {
    print_header "Test 5: Stress Test"
    run_ab_test "stress_test" \
        "$BASE_URL/api/search?keyword=kubernetes" \
        10000 200
}

test_dashboard() {
    print_header "Test 6: Dashboard Load"
    run_ab_test "dashboard" \
        "$BASE_URL/" \
        500 10
}

# Quick test suite
quick_test() {
    print_header "🚀 Quick Load Test Suite"
    check_service
    test_basic_search
    test_filtered_search
    print_success "Quick test completed!"
}

# Full test suite
full_test() {
    print_header "🚀 Full Load Test Suite"
    check_service
    test_basic_search
    test_filtered_search
    test_paginated_search
    test_high_concurrency
    test_dashboard
    print_success "Full test completed!"
}

# Stress test suite
stress_test() {
    print_header "🚀 Stress Test Suite"
    check_service
    test_high_concurrency
    test_stress
    print_success "Stress test completed!"
}

# Generate summary report
generate_report() {
    print_header "📊 Generating Summary Report"
    
    local report_file="$RESULTS_DIR/summary_$(date +%Y%m%d_%H%M%S).md"
    
    cat > "$report_file" << EOF
# Load Test Summary Report

**Date:** $(date)
**Target:** $BASE_URL

## Test Results

EOF
    
    for file in "$RESULTS_DIR"/*.txt; do
        if [ -f "$file" ]; then
            local test_name=$(basename "$file" .txt)
            local rps=$(grep "Requests per second" "$file" | awk '{print $4}')
            local mean_time=$(grep "Time per request" "$file" | head -1 | awk '{print $4}')
            local p95=$(grep "95%" "$file" | awk '{print $2}')
            local failed=$(grep "Failed requests" "$file" | awk '{print $3}')
            
            cat >> "$report_file" << EOF
### $test_name

- **Requests/sec:** $rps
- **Mean time:** ${mean_time}ms
- **95th percentile:** ${p95}ms
- **Failed requests:** $failed

EOF
        fi
    done
    
    print_success "Report generated: $report_file"
}

# Main
main() {
    local mode=${1:-full}
    
    case $mode in
        quick)
            quick_test
            ;;
        stress)
            stress_test
            ;;
        full)
            full_test
            ;;
        report)
            generate_report
            ;;
        *)
            echo "Usage: $0 {quick|full|stress|report}"
            echo ""
            echo "  quick  - Quick test (2 scenarios, ~2 min)"
            echo "  full   - Full test suite (6 scenarios, ~5 min)"
            echo "  stress - Stress test (high load, ~3 min)"
            echo "  report - Generate summary report"
            exit 1
            ;;
    esac
    
    echo ""
    print_info "Results saved in: $RESULTS_DIR/"
    print_info "Run './load-test.sh report' to generate summary"
}

# Run
main "$@"
