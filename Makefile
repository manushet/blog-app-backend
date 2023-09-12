docker-up:
	docker-compose up --build -d

docker-exec-php:
	docker exec -it blog-api-app_blog-php_1 bash

docker-down:
	docker-compose down --remove-orphans
