# The EmailAuthBundle

This bundle provides a way to authenticate a registered user by email only.
A magic link is send to the user where this request can be accepted or rejected.

[![build status](https://travis-ci.org/Scribblerockerz/email-auth-bundle.svg?branch=master)](https://travis-ci.org/Scribblerockerz/email-auth-bundle) 

**Attention:** This bundle is currently not ready for production!

This bundle supports symfony 4 only.

## Installation

### 1. Download the Bundle

```
composer require rockz/email-auth-bundle
```

### 2. Configuration

Configure the firewall by adding the `rockz_email_auth` key to it. Provide a user provider which should be used for the authentication procedure.
```yaml
# /config/packages/security.yaml
security:
  firewalls:
    main:
      rockz_email_auth: ~
```

Import bundle specific routes.
```yaml
# /config/routes/rockz_email_auth.yaml
_some_routing_key:
  resource: "@RockzEmailAuthBundle/Resources/config/routes.xml"
```

### 3. Prepare your template

Insert this minimum form somewhere on your page.
```html
<form action="" method="post">
    <input type="text" name="email_auth">
</form>
```
The request must be a post, with the provided `email_auth` parameter containing the users email. 

### 4. Update your database

Generate migration or update your database schema right away:
```bash
# Quick update
bin/console doctrine:schema:update --force # don't do this in production

# or generate migrations
bin/console doctrine:migrations:diff
bin/console doctrine:migrations:migrate 
```  

### 5. Configure your SwiftMailer

This bundle uses the SwiftMailer to send emails to the user which is provided by the configured user provider.

Documentation: [SwiftMailer configuration](https://symfony.com/doc/current/reference/configuration/swiftmailer.html) 

### 6. CSRF Protection (optional)
You can enable csrf protection for your login form.

Enable the `csrf_protection` under your firewall settings for `rockz_email_auth`.
```yaml
# /config/packages/security.yaml
security:
  firewalls:
    main:
      rockz_email_auth:
        csrf_protection: true
```

Add the following part to your login form:
```html
<input type="hidden" name="_csrf_token" value="{{ csrf_token('rockz_email_auth_authenticate') }}">
```

If you haven't required `symfony/form` you may do this by running
```
composer require symfony/form
```
It contains twig's `csrf_token` helper method.

## Configuration

Most of the bundle behaviour is configured inside the firewall configuration in the security section.
```yaml
# /config/packages/security.yaml
security:
    firewalls:
        main:
            rockz_email_auth:
                
                # Required to remember an authentication between requests
                remember_me:          true
                
                # Service id of handlers
                pre_auth_success_handler: ~
                pre_auth_failure_handler: ~
                success_handler:      ~
                failure_handler:      ~
                
                # input field parameter from the form/request
                email_parameter:      email_auth
                
                # redirect the user to this path/route if the user hits a restricted area
                initial_redirect:     /access
                
                # redirect the user to this path/route after an authorization request is sent
                pre_auth_success_redirect: /waiting
                
                # redirect the user to this path/route after an authorization request was rejected by the system
                pre_auth_failure_redirect: '/#partial_failure'
                
                # redirect the user to this path/route after an authorization request was accepted by the user
                success_redirect:     /
                
                # redirect the user to this path/route after an authorization request was rejected by the system or the user
                failure_redirect:     '/#total_failure'
                
                # bundle's core service for remote authorizations
                remote_authorization:
                    authorize_route:      rockz_email_auth_authorization_authorize
                    refuse_route:         rockz_email_auth_authorization_refuse
                    from_email:           changeme@example.com
                    template_email_authorize_login: '@RockzEmailAuth/emails/authorization/login.html.twig'
                
                # optional csrf protection, requires symfony/form package
                csrf_protection:      false
                csrf_token_id:        rockz_email_auth_authenticate
                csrf_parameter:       _csrf_token

```


## Example Setup

TBD.

The following part should explain how this bundle is supposed to be used.

```yaml
# /config/packages/security.yaml
security:
    providers:
        in_memory_members:
            memory:
                users:
                    john@example.com:
                        roles: ROLE_USER
                    emely@example.com:
                        roles: ROLE_USER
    firewalls:
        # custom firewall for the email authentication
        premium_firewall:
            # your user provider goes here (can be anything that provides a user)
            provider: in_memory_members
            
            # actual bundle specific configuration
            rockz_email_auth:
                remote_authorization:
                    from_email: "john.fox@example.com"
            
            # support logout
            logout:
                path:   /logout
                target: /
            
            # allow anonymous users to reach any routes
            anonymous: ~
        #...
    access_control:
        - { path: ^/premium, roles: ROLE_USER }
        - { path: ^/account, roles: ROLE_USER }
```

Import routes for the authorization controller. Create that file (btw. you can name it how ever you want).
```yaml
# /config/routes/rockz_email_auth.yaml
_some_routing_key:
  resource: "@RockzEmailAuthBundle/Resources/config/routes.xml"

# previously configured logout action needs this path  
logout:
    path: /logout
```