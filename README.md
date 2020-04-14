# Judex
> Judex v2 is api service which provides api for testing small programs implementing some algorithms and data structures.

************************************
# Installation
## Production

You need python3.6+ and pip

Feel free to use virtual enviroment provided by python if you want

### *Environment configuration*

Set up application configuration

```export APP_CONFIG_OBJECT=config.ProductionConfig```

### *Install requirements*

```pip install -r requirements/production.txt```

### *Initialize database*

You need to set up ```SQLALCHEMY_DATABASE_URI``` environment variable


If you want to use mysql

```pip install pymysql```

You need to create mysql database, mysql user <username> available for connection from localhost and grant privileges to it:

```CREATE DATABASE judex_db;```

```CREATE USER '<username>'@'localhost' IDENTIFIED BY '<password>';```

```GRANT ALL ON judex_db.* TO '<username>'@'localhost';```

```export SQLALCHEMY_DATABASE_URI="mysql+pymysql://<username>:<password>@localhost/judex_db"```


If you want to use sqlite:

```export SQLALCHEMY_DATABASE_URI="sqlite:///<path_to_your_sqlite_database_file>"```


Create tables in database:

```python manage.py db upgrade```

### Run application
```gunicorn --workers=<simultaniously_connected_clients_count> --bind '0.0.0.0:5050' manage:app```

Be careful with *<simultaniously_connectted_clients_count\>* because every client is available to use too much memory, that can result in significant system slowdown or even application failure. I recommend to set this value like this:

```simultaniously_connected_clients_count = floor(<amount of ram that you want to give the application> / 256)```
