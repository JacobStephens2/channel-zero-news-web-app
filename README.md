# The Channel 0 News

A web-based party game where players submit improvised responses to prompts and then perform them live in a carousel-style presentation. One player reads (the anchor), while another controls the teleprompter.

Created in collaboration with Eric Boerman. Most of the development was completed on a private repository and as such is not shown here.

## How to Play

1. **Host**: Cast `channelzeronews.stephens.page/host` on a screen the whole group can see. Enter the participants' names on the page.
2. **Players**: Go to `channelzeronews.stephens.page`, choose your name from the drop down, and fill out the prompts.
3. After all players have submitted their answers, the host clicks "Start the game!"
4. **Game**: Page through the slides, alternating who is reading the prompts as indicated.

## Setup

### Requirements

- PHP 7.4+ with MySQLi (and mysqlnd driver)
- MySQL / MariaDB
- Apache with `mod_rewrite` enabled

### Database

Create a MySQL database and the required tables:

```sql
CREATE DATABASE ChannelZeroNews;

CREATE TABLE tblPrompts (
    id INT PRIMARY KEY AUTO_INCREMENT,
    prompt1 TEXT, prompt2 TEXT, prompt3 TEXT, prompt4 TEXT,
    prompt5 TEXT, prompt6 TEXT, prompt7 TEXT
);

CREATE TABLE tblResponses (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255),
    prompts_id INT,
    partner VARCHAR(255),
    response1 TEXT, response2 TEXT, response3 TEXT, response4 TEXT,
    response5 TEXT, response6 TEXT, response7 TEXT, response8 TEXT
);
```

### Configuration

Copy the example config and fill in your credentials:

```bash
cp private/environmentVariables.example.php private/environmentVariables.php
```

Edit `private/environmentVariables.php` with your database credentials, or set environment variables:

| Variable | Description | Default |
|---|---|---|
| `DB_HOST` | Database host | `localhost` |
| `DB_USERNAME` | Database user | `ChannelZeroNews` |
| `DB_PASSWORD` | Database password | *(none)* |
| `DB_NAME` | Database name | `ChannelZeroNews` |
| `HOST_PASSWORD` | Password for host page (optional) | *(none, no auth)* |
| `APP_DEBUG` | Show PHP errors in browser | `false` |

### File Structure

```
index.php                  # Player submission page
host.php                   # Host control panel
game.php                   # Game carousel display
host.js                    # Host page interactivity
private/
  initialize.php           # App bootstrap (DB, sessions, CSRF)
  functions.php            # DB query helpers and output escaping
  environmentVariables.php # Local config (gitignored)
endpoints/
  getNumberOfPlayerSubmissions.php  # AJAX polling endpoint
components/
  innerHead.html           # Shared HTML head (meta, styles, scripts)
style/
  siteWideStyle.css        # Main styles
  carousel.css             # Game carousel styles
```

## Security

- All database queries use **prepared statements** (parameterized queries)
- All user-generated output is escaped with `htmlspecialchars()` (XSS prevention)
- All POST forms include **CSRF tokens**
- Host page supports optional **password authentication**
- Database credentials are loaded from environment variables
- Error display is disabled by default (enable with `APP_DEBUG=true`)

## Credits

- **Development**: Jacob & Eric
- **Game Design & Writing**: Eric
- **Web Development & Graphic Design**: Jacob
