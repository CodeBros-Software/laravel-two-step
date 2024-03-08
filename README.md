# Laravel 2 Step Verification

Table of contents:
- [About](#about)
- [Features](#features)
- [Requirements](#requirements)
- [Installation Instructions](#installation-instructions)
- [Configuration](#configuration)
    - [Environment File](#environment-file)
- [Usage](#usage)
- [Routes](#routes)
- [Screenshots](#screenshots)
- [File Tree](#file-tree)
- [Future](#future)
- [Opening an Issue](#opening-an-issue)
- [License](#license)

### About
Laravel 2-Step verification is a package to add 2-Step user authentication to any Laravel project easily. It is configurable and customizable. It uses notifications to send the user an email with a 4-digit verification code.

Laravel 2-Step Authentication Verification for Laravel. Can be used in out the box with Laravel's authentication scaffolding or integrated into other projects.

This package has been originally been created by Jeremy Kenedy.

### Features

| Laravel 2 Step Verification Features |
| :------------ |
| Uses [Notification](https://laravel.com/docs/5.5/notifications) Class to send user code to users email |
| Can publish customizable views and assets |
| Lots of [configuration](#configuration) options |
| Uses Language [localization](https://laravel.com/docs/5.5/localization) files |
| Verificaton Page |
| Locked Page |

### Requirements
* [Laravel 8+](https://laravel.com/docs/installation)

### Installation Instructions
1. From your projects root folder in terminal run:

    Laravel 6+ use:

    ```bash
        composer require codebros-nl/laravel-two-step
    ```

2. Register the package

Uses package auto discovery feature, no need to edit the `config/app.php` file.

3. Publish the packages views, config file, assets, and language files by running the following from your projects root folder:

```bash
    php artisan vendor:publish --tag=laravel-two-step
```

4. Optionally Update your `.env` file and associated settings (see [Environment File](#environment-file) section)

5. Run the migration to add the verifications codes table:

```php
    php artisan migrate
```

* Note: If you want to specify a different table or connection make sure you update your `.env` file with the needed configuration variables.

6. Make sure your apps email is configured - this is usually done by configuring the Laravel out the box settings in the `.env` file.

### Configuration
Laravel 2-Step Verification can be configured in directly in `/config/laravel-two-step.php` or in the variables in your `.env` file.

##### Environment File
Here are the `.env` file variables available:

```bash
LARAVEL_2STEP_ENABLED=true
LARAVEL_2STEP_DATABASE_CONNECTION=mysql
LARAVEL_2STEP_DATABASE_TABLE=laravel-two-step
LARAVEL_2STEP_USER_MODEL=App\Models\User
LARAVEL_2STEP_EMAIL_FROM="anEmailIsrequired@email.com"
LARAVEL_2STEP_EMAIL_FROM_NAME="Laravel 2 Step Verification"
LARAVEL_2STEP_EMAIL_SUBJECT='Laravel 2 Step Verification'
LARAVEL_2STEP_EXCEEDED_COUNT=3
LARAVEL_2STEP_EXCEEDED_COUNTDOWN_MINUTES=1440
LARAVEL_2STEP_VERIFIED_LIFETIME_MINUTES=360
LARAVEL_2STEP_RESET_BUFFER_IN_SECONDS=300
LARAVEL_2STEP_CSS_FILE="css/laravel2step/app.css"
LARAVEL_2STEP_APP_CSS_ENABLED=false
LARAVEL_2STEP_APP_CSS="css/app.css"
LARAVEL_2STEP_BOOTSTRAP_CSS_CDN_ENABLED=true
LARAVEL_2STEP_BOOTSTRAP_CSS_CDN="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css"
```

### Usage
Laravel 2-Step Verification is enabled via middleware.
You can enable 2-Step Verification in your routes and controllers via the following middleware:

```php
['middleware' => 'two-step']
```

Example to start recording page views using middlware in `web.php`:

```php
Route::group(['middleware' => 'two-step'], function () {
    Route::get('home', [Controller::class, 'home'])->name('home');
});
```

### Routes
* ```/verification/needed```
* ```/verification/verify```
* ```/verification/resend```

### Screenshots
![Verification Page](https://s3-us-west-2.amazonaws.com/github-project-images/laravel2step/1-verification-page.jpeg)
![Resent Email Modal](https://s3-us-west-2.amazonaws.com/github-project-images/laravel2step/2-verification-email-resent.jpeg)
![Lock Warning Modal](https://s3-us-west-2.amazonaws.com/github-project-images/laravel2step/3-lock-warning.jpeg)
![Locked Page](https://s3-us-west-2.amazonaws.com/github-project-images/laravel2step/4-lock-screen.jpeg)
![Verification Email](https://s3-us-west-2.amazonaws.com/github-project-images/laravel2step/5-verification-email.jpeg)

### Future
* Its own HTML email template.
* Add in additional notifications for SMS through Spryng
* Add in capture IP Address.
* Change to incremental tables and logic accordingly
    * Create Artisan command and job to prune said entries.

### Opening an Issue
Before opening an issue there are a couple of considerations:
* A **star** on this project shows support and is way to say thank you to all the contributors. If you open an issue without a star, *your issue may be closed without consideration.* Thank you for understanding and the support. You are all awesome!
* **Read the instructions** and make sure all steps were *followed correctly*.
* **Check** that the issue is not *specific to your development environment* setup.
* **Provide** *duplication steps*.
* **Attempt to look into the issue**, and if you *have a solution, make a pull request*.
* **Show that you have made an attempt** to *look into the issue*.
* **Check** to see if the issue you are *reporting is a duplicate* of a previous reported issue.
* **Following these instructions show me that you have tried.**
* If you have a questions send me an email to jeremykenedy@gmail.com
* Need some help, I can do my best on Slack: https://opensourcehelpgroup.slack.com
* Please be considerate that this is an open source project that I provide to the community for FREE when openeing an issue. 

Open source projects are a the community’s responsibility to use, contribute, and debug.

### License
Laravel 2-Step Verification is licensed under the MIT license. Enjoy!

