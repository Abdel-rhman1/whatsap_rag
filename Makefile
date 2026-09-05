up:
docker-compose up -d

down:
docker-compose down

logs:
docker-compose logs -f

shell:
docker-compose exec app bash

migrate:
docker-compose exec app php artisan migrate --seed

ollama-pull:
docker-compose exec ollama ollama pull nomic-embed-text
docker-compose exec ollama ollama pull qwen2.5:0.5b

setup:
cp .env.example .env
docker-compose build
docker-compose up -d
docker-compose exec app composer install
docker-compose exec app php artisan key:generate
docker-compose exec app php artisan migrate --seed
