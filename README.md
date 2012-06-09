PhpProject1
===========

As an unauthenticated user
	I can ask for the api version
	I can request an nonce from the login server

I can not use a service without a session token
I can not get a session token by providing my user name and password in the clear
I can get my session token by providing my user name and password by hashing them with SHA-1 and nonce
I can not use the same nonce multiple times
I must use nonce within 1 minute
I can only use the nonce with my own account
I can only have 1 session token active for each user name
I can have an active session for up to 20 minutes without any activity

    I can notify the login server that my session is still active

I can use the session token to retrieve my user name
I can use the session token to retrieve my email
I can use the session token to retrieve my Full Name
I can use the session token to retrieve my First Name
I can use the session token to retrieve my Last Name
I can use the session token to retrieve my Session Start Time
I can use the session token to retrieve my IP address

IP restriction?
Security Token/email
suspension
hacking/intrusion detection/brute force attacks

Creating new accounts
managing existing account
impersonation
resetting child accounts
