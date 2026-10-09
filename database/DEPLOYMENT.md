# CyberPanel database connection

Website: `monitoring.bcp-sms.com`

| Setting | Value |
| --- | --- |
| Host | `localhost` (if MySQL and the website are on the same server) |
| Port | `3306` |
| Database | `moni_test` |
| User | `moni_monitoring` |
| Password | Stored in the private `database/config.local.php` file |

The database and username include CyberPanel's `moni_` prefix. The website
domain and server's public IP are not needed as the MySQL host when PHP and
MySQL run on that same server.

## Deploy from GitHub

1. Push the connection changes to the `Monitoring` branch used by this website.
2. Pull/deploy that branch in CyberPanel, unless its automatic deployment has
   already deployed the new commit. Connecting a repository alone does not
   confirm that every push automatically deploys.
3. Upload the private `database/config.local.php` from your computer into the
   deployed project's `database` folder. It is intentionally excluded from Git,
   so a Git pull will not create it. Alternatively, set the database environment
   variables listed below in the website's PHP environment.
4. Follow the import and verification steps below.

## Alternative: upload the connection update

1. Open CyberPanel, select `monitoring.bcp-sms.com`, and open its File Manager.
2. Open the deployed project root: the folder containing `index.php`, `auth`,
   `database`, and `modules` (normally the website's `public_html` folder).
3. Download backup copies of the files being replaced to your computer.
4. Upload and extract the prepared connection ZIP into that project root,
   preserving its `database/` and `modules/` paths and replacing the matching
   files. If extraction creates a wrapper folder, move its contents into the
   project root. Include `database/config.local.php`; it is intentionally
   ignored by Git and will not arrive through a Git deployment.
5. Remove the uploaded ZIP from the public website folder after extraction.
   It contains the private database password. Keep any backup ZIPs off the
   public website as well.

This update connects the main login, `Database2`, and monitoring models to the
same configuration. Other modules with their own independent connection files
are not reconfigured by this update.

Existing server environment variables override the private configuration file.
If the server already defines database variables, update them to these values:

```text
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=moni_test
DB_USER=moni_monitoring
DB_PASSWORD=<the database password>
```

`DB_NAME` and `DB_PASS` are also supported as aliases. An empty `DB_PASSWORD`
means an intentionally empty password and takes precedence over `DB_PASS`.

## Import tables if the database is empty

Creating a database in CyberPanel does not import the application's tables.
For a new empty database, select `moni_test` in phpMyAdmin and import the prepared
`moni_test_import.sql` file, or use a current export from the working system if
it has newer records. Do not import over an existing populated database. This
SQL file includes data as well as table definitions. Upload it only to the
database import screen, not to the website's public folder.

The prepared import is based on `modules/payr_bcp.sql`. Its database-creation
statement has been removed, `USE` targets `moni_test`, and `DROP TABLE` statements
have been removed. The original export must not be imported unchanged: it tries
to create and select `payr_bcp` and drops existing tables.

The older `database/update/sms (1).sql` file does not contain the `em_employees`,
`em_roles`, and `em_departments` tables required by the current main login.

## Verify on the server

From the deployed project root, run with the website's PHP installation:

```sh
php database/check_connection.php
```

The command only reads the schema and checks the session time zone. It does not
modify data, import SQL, or display passwords. It confirms the selected database,
required tables, and main-login columns; it does not test a user's login or every
application query. It deliberately returns HTTP 404 if opened in a browser.

Finally, sign in through the website with an existing **application employee
account** and check the monitoring dashboard. The database username and password
are MySQL credentials, not an application login.
