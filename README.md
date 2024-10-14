code style = psr12

wanted to use a lot of 3rd party packages for different things but not part of project so made notes here

mysql setup
not currently using migrations (3rd party package), so set up base tables with the following

    CREATE DATABASE points_demo;

    USE points_demo;
    
    CREATE TABLE users
    (
    id INT unsigned AUTO_INCREMENT,  
    name VARCHAR(255),
    email VARCHAR(255),
    points_balance int,
    PRIMARY KEY(id)
    );
    
    INSERT INTO users ( name, email, points_balance) VALUES
    ( 'moo', 'moo@gmail.com', 0 ),
    ( 'cow', 'cow@gmail.com', 1000 );

server setup

    php -S localhost:8888

testing setup

    vendor/bin/phpunit tests --colors=always
    also using requests.http to manually get api reponses (storm's answer to postman)

security notes

    could use vlucas/phpdotenv (3rd party package) and .env file to secure the db vars
    could set envs and use getenv() for db vars
    should be https
    could use csrf package for POST, PUT, DELETE
    could use lcobucci/jwt (3rd party package) for jwt

validation notes

    could use DavidePastore/Slim-Validation (3rd party package) for better validation

container notes

    could use a 3rd party package for a container but that wasnt in test requirements so used a generic container

other notes

    description was a requirement for earn and redeem but just validates and shows message in success response as no db field or logging
    could be other ways to show success message in response, like returning the posted user, updated user after earn or redeem
