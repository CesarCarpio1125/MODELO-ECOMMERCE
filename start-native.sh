#!/bin/bash

echo "=== Starting Native/Electron Development Environment ==="

# Start Native backend in background
echo "Starting Native backend..."
php artisan native:run &
NATIVE_PID=$!

# Wait a moment for backend to start
sleep 3

# Start frontend with Native config
echo "Starting frontend for Native..."
pnpm run dev:native &
FRONTEND_PID=$!

echo "=== Environment Started ==="
echo "Native Backend: http://127.0.0.1:8100"
echo "Frontend Dev: http://127.0.0.1:5173"
echo ""
echo "Both services are running in background"
echo "Press Ctrl+C to stop both services"
echo ""
echo "Use Ctrl+Shift+D in Electron for debugging"

# Function to cleanup on exit
cleanup() {
    echo ""
    echo "Stopping services..."
    kill $NATIVE_PID 2>/dev/null
    kill $FRONTEND_PID 2>/dev/null
    echo "All services stopped"
    exit 0
}

# Trap Ctrl+C
trap cleanup INT

# Wait for processes
wait
