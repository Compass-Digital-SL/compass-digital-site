# The Studio — owner guide

The **Studio** is your branded, simplified admin for running the site day to day:
projects (case studies), testimonials/reviews, quote enquiries, and — for you —
Site Settings and your team. It is the normal WordPress admin underneath, but
reskinned and stripped down so you only ever see what you manage.

## One link to remember

```
https://YOUR-SITE/studio
```

- Not signed in → you get the branded login.
- Signed in → you land straight on **Projects**.

Bookmark it. You never need the WordPress dashboard.

## First-time setup (one-off, done by whoever deploys the site)

Your Studio Owner account is created once with WP-CLI on the server (cPanel →
Terminal, or SSH):

```
wp compass studio_owner --user=YOUR_USERNAME --email=you@example.com
```

- This prints a **generated password** — copy it, that's your first login.
- Or choose your own: add `--pass='A-Strong-Password'`.
- Re-running it on an existing user promotes them to Studio Owner and resets the
  password, so it's also your "locked out" recovery path.

Then sign in at `/studio` and (optionally) change the password to something you'll
remember — see below.

## Changing your own password

1. In the Studio, open **Account** (left menu).
2. Scroll to **New Password** → **Generate Password** (or type your own) → **Update
   Profile**.

## Adding a team member (staff)

Staff can manage **projects, testimonials and quote requests**. They **cannot**
change or reset any password, and cannot add users — only you can.

1. Open **Team** (left menu).
2. Under **Add a team member**: enter their full name, a username, an optional
   email, and a **password you choose** (at least 10 characters).
3. **Add member.** Give them the username + password and the `/studio` link.

To change a staff password later: **Team → Set password** next to their name.
To remove someone: **Team → Remove** (their projects stay, reassigned to you).

Because staff can't use the "lost your password?" link either, you are always the
only person who controls their access.

## Who sees what

| | Studio Owner (you) | Studio Staff | Developer (administrator) |
|---|---|---|---|
| Projects / Testimonials / Quote Requests | ✅ | ✅ | ✅ |
| AI "Draft with AI" | ✅ | ✅ | ✅ |
| Site Settings | ✅ | — | ✅ |
| Team (add/edit staff) | ✅ | — | ✅ |
| Change own password | ✅ | — | ✅ |
| Full WordPress admin | — (hidden) | — (hidden) | ✅ |

## Honest note on security

`/studio` is a tidy, branded front door, but the standard WordPress login page
still exists at its usual address — we just don't send you there. Fully hiding it
is a separate, careful change we can do later if you want it. Your accounts are
protected by WordPress's normal login either way; use strong passwords.
