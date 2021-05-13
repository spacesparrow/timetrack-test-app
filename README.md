# **Set up project**

 - Clone repository using SSH `git@github.com:spacesparrow/timetrack-test-app.git {your directory}`
 - Navigate to project directory that was created at the previous step `cd {project directory}`
 - Build containers `docker-compose build`
 - Up containers `docker-compose up -d`
 - Install packages from composer `docker-compose exec php compose install`
 - Migrate database schema `docker-compose exec php php bin/console doctrineLmigrations:migrate`
 - Generate keypair for JWT token authentication `docker-compose exec php php bin/console lexik:jwt:generate-keypair`
 - Project set up finished, host is `http://localhost`

# **API Docs**

 - API docs can be found at this URL `http://localhost/api/doc`

# **Run tests**

 - For unit test suite execute this command `docker-compose exec php composer run test-unit`