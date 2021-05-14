# **Set up project**

- Clone repository using SSH `git@github.com:spacesparrow/timetrack-test-app.git {your directory}`
- Navigate to project directory that was created at the previous step `cd {project directory}`
- Build containers `docker-compose build`
- Up containers `docker-compose up -d`
- Add local .env file `docker-compose exec php cp .env .env.local `
- Install packages from composer `docker-compose exec php composer install`
- Migrate database schema `docker-compose exec php php bin/console doctrine:migrations:migrate`
- Generate keypair for JWT token authentication `docker-compose exec php php bin/console lexik:jwt:generate-keypair`
- Project set up finished, host is `http://localhost`

# **API Docs**

- API docs can be found at this URL `http://localhost/api/doc`

# **Tests and code style**

- For running unit test suite execute `docker-compose exec php composer test-unit`
- For checking code style execute `docker-compose exec php composer check-cs`
- For fixing code style execute `docker-compose exec php composer fix-cs`

# **Demo launch**

There are data prepared for demo launch.
Run `docker-compose exec php php bin/console doctrine:fixtures:load --group=demo` to fill database with it. Please note
that all data stored in database will be purged after fixtures loading

# **Managing .env**

By default, Docker load env variables from `.env` file located in the same directory as `docker-compose.yml`.

To override these variables, Docker brings options `--env-file=path/to/file` which should be passed to
each `docker-compose` command you running. For example: `docker-compose --env-file=.env.local build` will build
containers with vars mentioned in `.env.local`

### Note

If you built and up docker containers with different `APP_URL` or `NGINX_PORT` variables, your host and API docs URL
will be changed

### Possible improvements plan
- Cover forms by unit tests (`\App\Form` namespace)
- Add functional tests
- Add report file generation by chunks to reduce memory usage
- Split task and time spent to separate entities to allow adding time in existing task to avoid duplications
- Add code style check on commit event
- Add tests launching pipelines on request creation
