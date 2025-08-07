#!/bin/bash

# Kill and remove all devcontainer containers (more aggressive)
echo "Force stopping all devcontainer containers..."
docker ps -q | xargs -r docker kill 2>/dev/null || true

echo "Removing all containers..."
docker ps -aq | xargs -r docker rm -f 2>/dev/null || true

# Remove all devcontainer volumes
echo "Removing all devcontainer volumes..."
docker volume ls -q | grep -E "acorn_devcontainer|devcontainer_acorn|devcontainer" | xargs -r docker volume rm -f 2>/dev/null || true

# Clean up any dangling resources
echo "Cleaning up dangling resources..."
docker system prune -f --volumes 2>/dev/null || true

# Wait a moment for ports to be released
sleep 2

# Check if anything is still running on port 8080
if lsof -i :8080 >/dev/null 2>&1; then
    echo "Warning: Something is still running on port 8080"
    lsof -i :8080
else
    echo "Port 8080 is clear"
fi

echo "Devcontainer cleanup complete!"