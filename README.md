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