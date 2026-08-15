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
server-enter:
	@ssh root@${SERVER_IP}

install:
	echo "Docker Exec";\
	docker exec -it php-ban composer install
enter:
	docker exec -it php-ban /bin/bash;
nginx:
	docker exec -it nginx-ban /bin/sh;	
clear:
	@docker exec -it php-ban /bin/bash -c \
"php artisan config:cache && php artisan config:clear && php artisan horizon:terminate && php artisan queue:restart && php artisan route:clear && php artisan route:cache"

ngrok:
	@ngrok http --host-header=rewrite http://localhost:8080;


import-db:
	@docker exec -i ${DB_HOST} mysql -u user -ppassword -e "DROP DATABASE IF EXISTS ${DB_DATABASE}; CREATE DATABASE ${DB_DATABASE};"
	@docker exec -i ${DB_HOST} mysql -u user -ppassword ${DB_DATABASE} < storage/app/gan.sql
	@$(MAKE) update-bulk-passwords

db-export:
	@docker exec -i ${DB_HOST} mysqldump -u ${DB_USERNAME} -p${DB_PASSWORD} --no-tablespaces ${DB_DATABASE} > storage/app/gan.sql

update-bulk-passwords:
	@docker exec -i php-ban php artisan users:update-bulk-passwords --force
