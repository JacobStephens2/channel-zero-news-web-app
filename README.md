# The Channel 0 News (PHP — historical)

**This repository is historical and is not deployed.**

The live game is the Rust re-platform:

- **Repo:** [JacobStephens2/channel-zero-news](https://github.com/JacobStephens2/channel-zero-news)
- **Play:** [https://zero.stephens.page](https://zero.stephens.page)

This tree is the original **PHP/MySQL** app (HTTP polling, Apache). It was created in collaboration with Eric Boerman. Most early development happened on a private repository and is not fully reflected here.

Keep this archive for archaeology and comparison with the rewrite. Do not treat it as the production source.

---

## Original README (context)

A web-based party game where players submit improvised responses to prompts and then perform them live in a carousel-style presentation. One player reads (the anchor), while another controls the teleprompter.

### How it worked (legacy hosts)

Older docs referred to `channelzeronews.stephens.page`. Production now lives at `zero.stephens.page` on the successor stack.

### Stack (this archive only)

- PHP 7.4+ with MySQLi
- MySQL / MariaDB
- Apache with `mod_rewrite`

See git history for schema, endpoints, and the old host/submission/performance flow.
