# Roadmap

This is more of a todo for this project.

- Cleanup services.xml
    - Declare public services
    - Add aliases for auto-wiring
- Write tests
- Entities Best Practise.. maybe define them with xml or yaml?
- Allow configuration
- Change Name back to EmailAuthBundle an remove the previous one...


- write a setup guide
    - firewall configuration:
        rockz_email_auth: ~
    - routing
        ```yaml
        _rockz_email_auth:
          resource: "@RockzEmailAuthBundle/Resources/config/routes.xml"
        ```
    - form:
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