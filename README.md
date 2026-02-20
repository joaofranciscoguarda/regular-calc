# Regular Calculator

## ⚠️ Warning

This is **not** a production-level containerized setup.
It was intentionally kept simple for the purpose of this technical test.

---

## Setup

### Using Laravel Sail

If you don’t have the `sail` alias configured, replace `sail` with:

```
./vendor/bin/sail
```

in all commands below.

---

### Install Dependencies

```
composer install
pnpm install
```

> **Note:** If you don’t have `pnpm` installed, run:
>
> ```
> npm install -g pnpm
> ```

---

## Running the Application

Start the containers and development server:

```
sail up -d
sail pnpm dev
sail artisan migrate
```

---

## Running Tests

While the application is running:

```
sail artisan test
```

---

## API Documentation

#### Routes:

Api URL: http://localhost/api/

- `GET /calculations`
-   - Returns pagianted list of user entries
- `POST /calculations`
-   - Body: {"expression": "2+2"}
- `DELETE /calculations/{id}`
- `DELETE /calculations/`

## [Postman](https://www.postman.com/) / [Bruno](https://docs.usebruno.com/) API Clients

Inside `api_docs` I'm providing the routes to be imported and tested through Postman or Bruno API clients.

Ps: For the love of simplicity, no over engineering to provide api docs.

---

# Personal Choices & Decision Making

Instead of spending a significant amount of time building a custom expression handler from scratch (for example, implementing a PEG parser manually) or creating multiple calculation endpoints, I decided to search for a package that properly fits the use case.

I chose:

- [`jcubic/expression`](https://packagist.org/packages/jcubic/expression#2.0.0)

It’s not a particularly popular package (around 5k downloads), which might raise eyebrows at first. However, it is built on top of:

- [`smuuf/php-peg`](https://packagist.org/packages/smuuf/php-peg)

This package is a continuation and improvement of the original `php-peg` project.

### Security Considerations

I did not blindly trust the package.

I:

- Read through the source code of both packages
- Intentionally tried to break the parser
- Looked for potential injection or execution vulnerabilities

One notable concern is that the original `php-peg` (from 2012) made heavy use of `eval()`, which is widely known to be dangerous if misused.

However:

- The maintained version by `smuuf` significantly reduced the use of `eval()`
- There is only **one remaining `eval()`**, used inside the compiler
- It is wrapped with a protection layer

To validate this further, I created:

```
/tests/Feature/ExpressionSecurityTest.php
```

This test intentionally attempts malicious inputs to ensure the system behaves safely.

---

## Visual Design

This one is simple and honest.

Earlier that day, I was thinking about **Regular Show**, so I decided to lean into that aesthetic for the interface — especially inspired by the intro visuals.

No deep UX philosophy here — just a creative choice I enjoyed.

---

# Architecture & File Organization

For review clarity, I removed unnecessary files and directories from both the front-end and back-end to keep the project focused and easy to navigate.

---

## Front-End

- Simple button grid configuration
- Display screen
- Side “ticker tape” history

You can:

- Click a previous expression in the ticker tape to paste it into the current input
- If no operator is provided before clicking, the system assumes `+`

The front-end is intentionally minimal and straightforward.

---

## Back-End

Both:

- Inertia + Vue interface
- API endpoints

Use the same `CalculationService`.

This service:

- Handles paginated history
- Stores calculations
- Associates them with a `sessionId`
- Caches results to prevent unnecessary recalculations

---

### API Session Handling

When using the API directly:

- The `EnsureApiSession` middleware generates a session ID
- It is stored in an HTTP-only, read-only cookie
- The cookie expires after 30 days

This allows:

- No login requirement
- Isolated per-user calculation history
- Persistent session-based tracking

---

## What’s the Trick?

The real trick is that `jcubic/expression` parses and evaluates mathematical expressions for us.

Since expressions are deterministic, the expression string itself becomes a unique caching key.

This means:

- No recalculation for identical expressions
- Efficient handling of deeply nested or complex operations

Feel free to stress test it.

Try:

```
-8(5/2)^2*(1-sqrt(4))-8
```

You can test it via the API or through the front-end.

(Yes, that one’s the answer to everything.)

### Thank you for using regular calc!

Have a blessed day!
