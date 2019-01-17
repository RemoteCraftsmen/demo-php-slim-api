# Todo App 
Basic todo app written in Slim

### Prerequisites
* Docker - v18.06.1

## Installation

#### Clone the repository
`git clone git@bitbucket.org:remotecraftsmen/php-slim-api.git`

#### app/docker setup
`./exec.sh configure`

#### Run the database migrations
`./exec.sh migrate`

#### Run tests
`./exec.sh test`

#### Generate Apidoc
`apidoc -c config/ -i app/Controllers/ -o apidoc/`

### DEMO
Live demo available at https://php-slim-api.rmtcfm.com/
Api documentation available at https://php-slim-api.rmtcfm.com/doc/ 
