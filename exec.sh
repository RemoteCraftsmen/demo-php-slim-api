#!/bin/bash

#-------------------#
#----- Helpers -----#
#-------------------#

usage() {
    echo "$0 [COMMAND] [ARGUMENTS]"
    echo "  Commands:"
    echo "  - up: rebuild and start all containers"
    echo "  - down: stop all containers"
    echo "  - configure: configure application"
    echo "  - php: run command inside php container"
    echo "  - composer: run command for composer"
    echo "  - recreate: recreate docker containers for env refresh"
    echo "  - dbrebuild: rebuild database"
}

fn_exists() {
    type $1 2>/dev/null | grep -q 'is a function'
}

COMMAND=$1
shift
ARGUMENTS="${@}"

#--------------------#
#----- Commands -----#
#--------------------#

up() {
    docker-compose up -d --build --remove-orphans;
}

down() {
    docker-compose down;
}

recreate() {
    docker-compose up -d --force-recreate;
}

configure() {
    if [ ! -f .env ]; then
        cp .env.example .env;
        curl -fsSL 'https://getcomposer.org/composer.phar' -o ./composer.phar;
        docker-compose up -d --force-recreate;
        docker-compose exec php php composer.phar install;
        recreate;
    fi;
}

php() {
    docker-compose exec php ${@};
}

composer() {
    if [ ! -f composer.phar ]; then
        configure;
    fi;
    docker-compose exec php php composer.phar --prefer-dist ${@};
}

migrate(){
    docker-compose exec php php vendor/bin/phinx migrate -c config/config-phinx.php
}

createmigration(){
    docker-compose exec php php vendor/bin/phinx create ${@} -c config/config-phinx.php
}

test(){
    docker-compose exec php ./vendor/bin/phpunit ./app/Tests/ --testdox
}
#---------------------#
#----- Execution -----#
#---------------------#

fn_exists $COMMAND
if [ $? -eq 0 ]; then
    $COMMAND "$ARGUMENTS"
else
    usage
fi
