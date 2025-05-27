#!/bin/bash
# Build and run containers
docker-compose up -d --build

# Wait for container
sleep 10

# Run Laravel setup
docker exec laravel-app php artisan key:generate
docker exec laravel-app php artisan migrate
