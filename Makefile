up:
	docker compose \
		-f docker-compose.dev.yaml \
	    up -d --build --remove-orphans
down:
	docker compose \
		-f docker-compose.dev.yaml \
	    down
dev:
	docker compose \
		-f docker-compose.dev.yaml \
	    up -d --build --remove-orphans

	concurrently -k \
    		-n api,watcher \
    		"symfony serve --no-tls --port 9301" \
    		"bin/console assetic:watch"
db:
	bin/console doctrine:schema:update --force

sync-dev-db:
	ssh eurotours mysqldump --add-drop-table -h 127.0.0.1 -P 9302 -u eurotours -peurotours eurotours |\
	mysql -h 127.0.0.1 -P 9302 -u eurotours -peurotours -D eurotours

deploy:
	git push && ssh eurotours ./eurotours/deploy.sh
