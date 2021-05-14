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

# **Tests and code style**

 - For running unit test suite execute `docker-compose exec php composer test-unit`
 - For checking code style execute `docker-compose exec php composer check-cs`
 - For fixing code style execute `docker-compose exec php composer fix-cs`