# Rabbit MQ simple test

## How does it work?

- Form will submit text to API filtering profanity.
- This is done through a queue managed by RabbitMQ
- Filtered messages are then stored in Redis, and displayed.

## Known issues

- Multi line messages break our code.