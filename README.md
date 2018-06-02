# Using EmailAuthBundle
This bundle provides a way to authenticate a user by email only.
An authorization request is sent to an email to ask the user if the requested authentication is intended/correct.
With a click in that email, the authentication attempt can be accepted or canceled. 

**Attention:** This bundle is currently not ready for production!

This bundle supports symfony 4 only.

## Installation

### 1. Download the Bundle

```
composer require rockz/email-auth-bundle
```

### 2. Configuration

Configure the firewall:
```yaml
# /config/packages/security.yaml
security:
  firewalls:
    main:
      rockz_email_auth: ~
```

Import bundle specific routes:
```yaml
# /config/routes/rockz_email_auth.yaml
_some_routing_key:
  resource: "@RockzEmailAuthBundle/Resources/config/routes.xml"
```

### 3. Prepare your template

Insert this form somewhere on your page:
```html
<form action="" method="post">
    <fieldset>
        <legend>Authenticate yourself by mail</legend>
        <label for="email_auth">Email</label>
        <input type="text" name="email_auth" id="email_auth">
    </fieldset>
    <button>submit</button>
</form>
```

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


## Example Setup

The following part should explain how this bundle is supposed to be used.

TBD.

The following firewall configuration is going to enable:
- authentication by email
- support logout
- add a restricted area


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
            
            # support to logout
            logout:
                path:   /logout
                target: /
            
            # allow anonymous users reach the any routes
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
  
logout:
    path: /logout
```