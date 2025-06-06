# Usage commands
up:
	docker-compose -f docker-compose.yml down && docker-compose -f docker-compose.yml up -d --remove-orphans

watch:
	docker-compose -f docker-compose.yml down && docker-compose -f docker-compose.yml up --remove-orphans	

stop:
	docker-compose -f docker-compose.yml stop

down:
	docker-compose -f docker-compose.yml down -v	
	
build:
	docker-compose -f docker-compose.yml down && docker-compose -f docker-compose.yml up -d --build --remove-orphans

magento-restore-db:
	docker-compose run deploy sh -c 'mysql -h db.magento2.docker -P 3306 -u root -p"magento2"  -e "drop database IF EXISTS magento2"'
	docker-compose run deploy sh -c 'mysql -h db.magento2.docker -P 3306 -u root -p"magento2"  -e "create database  IF NOT EXISTS magento2"'
	docker-compose run deploy sh -c 'mysql -h db.magento2.docker -P 3306 -u root -p"magento2"  magento2 < $(filter-out $@,$(MAKECMDGOALS))'
	


# Magento commands
magento-bash:
	 docker-compose run deploy

magento-composer:
	docker-compose run deploy sh -c 'php -d memory=-1 /.docker/composer.phar  $(filter-out $@,$(MAKECMDGOALS))'

magento-clear:
	docker-compose run deploy sh -c 'rm -rf pub/static/* rm -rf /var/di/ /var/generation/ var/view_preprocessed/ var/cache/ var/page_cache/ var/di/ var/generation/* /generated/*;php -d memory_limit=-1 bin/magento setup:upgrade; php -d memory_limit=-1 bin/magento cache:flush; php -d memory_limit=-1 bin/magento cache:clean; php -d memory_limit=-1 bin/magento setup:static-content:deploy en_US es_MX -f; php -d memory_limit=-1 bin/magento setup:di:compile; php -d memory_limit=-1 bin/magento cache:flush; php -d memory_limit=-1 bin/magento cache:clean; php -d memory_limit=-1 bin/magento index:reindex; php -d memory_limit=-1 bin/magento cron:run; chmod -R 777 var; chmod -R 777 generated; chmod -R 777 pub/static'	

magento-download:
	docker-compose run deploy sh -c 'php -d memory=-1 /.docker/composer.phar create-project --repository-url=https://repo.magento.com/ magento/project-community-edition .'

magento-sampledata:
	docker-compose run deploy sh -c 'php -d memory=-1 bin/magento sampledata:deploy; php -d memory=-1 bin/magento setup:upgrade;'

magento-test:
	docker-compose run deploy sh -c 'php bin/magento dev:tests:run unit'

magento-install:
	docker-compose run deploy sh -c 'rm -rf app/etc/env.php'	
	docker-compose run deploy sh -c 'mysql -h db.magento2.docker -P 3306 -u root -p"magento2"  -e "drop database IF EXISTS magento2"'
	docker-compose run deploy sh -c 'mysql -h db.magento2.docker -P 3306 -u root -p"magento2"  -e "create database  IF NOT EXISTS magento2"'
	docker-compose run deploy sh -c 'php bin/magento setup:install --base-url=http://localhost/ --db-host=db.magento2.docker --db-name=magento2 --db-user=root --db-password=magento2 --admin-firstname=Magento --admin-lastname=User --admin-email=user@example.com --admin-user=admin --admin-password=admin123 --language=en_US --currency=USD --timezone=America/Chicago --use-rewrites=1 --search-engine=elasticsearch7 --elasticsearch-host=opensearch.magento2.docker --elasticsearch-port=9200; chmod -R 777 app/'
	docker-compose run deploy sh -c 'cp .docker/env.php app/etc/env.php'
	docker-compose run deploy sh -c 'php bin/magento module:disable Magento_AdminAdobeImsTwoFactorAuth'
	docker-compose run deploy sh -c 'php bin/magento module:disable Magento_TwoFactorAuth'


# composer require --dev phpunit/phpunit || composer.json remove: "**/Test/**"
#  composer require --dev php-allure/allure-phpunit


# Docker commands
#	docker network prune
#	docker rm -vf $(docker ps -a -q); docker rmi -f $(docker images -a -q)

# Clear images and containers
#   docker system df
#   docker builder prune -f && docker image prune -a -f && docker volume prune -f


# Remove all databases
#   docker volume prune
#   docker container prune 