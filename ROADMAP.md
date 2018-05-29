# Roadmap

This is more of a todo for this project.

- Cleanup services.xml
    - Declare public services
    - Add aliases for auto-wiring
- Allow bundle configuration
- Add CSRF protection support
- Prevent users from sending too many requests
- Add registration workflow `#documentation`
    - Allow users to register by defining a seperate firewall for the registration path. It should give the developer the freedom to:
        - Change email template for that specific registration authorization: "Finish registration by mail — Didn't register for an account? Do nothing, or click on deny to block registrations with your email."
        - Custom user provider should be used to return a fresh user if no user was found. Or store a `guest` in the session and store the user in the database with the success handler.
        - After authorization, the user is authenticated.
        - Plus: allow the user to add additional information after he or she registered successfully by mail. 
