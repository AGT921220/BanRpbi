include .env

 example:
	@echo ${SERVER_PASS};
up:
	@docker compose down;\
	docker-compose up -d
up-dev:
	@docker compose down;\
	docker compose -f docker-compose-dev.yml up -d;
down-dev:
	@docker compose -f docker-compose-dev.yml down;
install:
	echo "Docker Exec";\
	docker exec -it php-ban composer install
enter:
	docker exec -it php-ban /bin/bash;
nginx:
	docker exec -it nginx-ban /bin/sh;	