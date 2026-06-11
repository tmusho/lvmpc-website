# Lightweight Non-Profit PHP Website

This is an extremely lightweight nonprofit website: one page with the essential information visitors need, including mission, services, impact, sponsors, donation links, contact details, and recent organization updates.

The site deliberately avoids the bloat and maintenance overhead of WordPress. It has no Composer, Node, database, admin dashboard, plugin stack, or build-step dependency. The deployable site lives in `src/` and renders a single responsive PHP page from `src/index.php`.

Recent events and announcements can be pulled from the organization's Facebook page through the Facebook Graph API, so staff can keep posting where they already work while the website stays simple.

<img width="1064" height="875" alt="image" src="https://github.com/user-attachments/assets/65274579-8fda-4103-b4bc-069dbd96d677" />

## Project Structure

```text
.
├── src/
│   ├── index.php          # Main website page, styles, and Facebook feed logic
│   ├── .htaccess          # Apache HTTPS, security header, and CSP rules
│   ├── images/            # Images used by the live website
└── README.md
```

For deployment, use `src/` as the document root. The current website does not require the database backup in `example/`.

## Requirements

- PHP 8.0 or newer recommended
- PHP cURL extension recommended for Facebook Graph API requests
- Apache with `mod_headers` and `mod_rewrite` if you want to use the included `src/.htaccess`

The site can still render without cURL because it falls back to `file_get_contents()`, but production servers commonly disable remote URL access for that function.

## Deploy to Web Hosting

This site is intended to replace a heavier WordPress install on basic shared hosting such as Hostinger or GoDaddy.

Before uploading the site:

1. Log in to your hosting dashboard.
2. Uninstall WordPress from the website if WordPress is currently installed.
3. Back up any existing files or database content you still need.
4. Open the hosting file manager, such as Hostinger hPanel File Manager, or enable SSH/SFTP upload.

Upload the contents of `src/` to:

```text
/public_html/
```

Upload the files inside `src/`, not the `src` folder itself. After upload, `/public_html/` should contain `index.php`, `.htaccess`, and the `images/` directory.

Make sure `.htaccess` is uploaded too. Some file managers hide dotfiles by default.

Then visit the domain in a browser. If the host supports Apache `.htaccess`, the included rules will redirect HTTP to HTTPS, set common security headers, enable HSTS, and define a Content Security Policy. If you add third-party scripts, images, forms, or frames, update the Content Security Policy in `.htaccess` so browsers allow those resources.

## Configuration

The page can read recent Facebook posts through the Facebook Graph API. Configure these environment variables on the server:

| Variable | Purpose | Default |
| --- | --- | --- |
| `FACEBOOK_PAGE_ID` | Facebook page ID to read posts from | LVMPC page ID |
| `FACEBOOK_PAGE_ACCESS_TOKEN` | Facebook Page access token used for Graph API requests | Built-in fallback value in source |
| `FACEBOOK_GRAPH_API_VERSION` | Graph API version path, such as `v23.0` | `v23.0` |
| `FACEBOOK_CACHE_TTL_SECONDS` | How long to reuse cached Facebook updates | `3600` |

For a public GitHub repository, review `src/index.php` before publishing and move real access tokens or private values into environment variables.

## Facebook Graph API Setup

The website reads posts from the organization's Facebook page with the Facebook Graph API. Official Meta documentation:

- [Graph API](https://developers.facebook.com/docs/graph-api/)
- [Pages API](https://developers.facebook.com/docs/pages-api/)
- [Access tokens](https://developers.facebook.com/docs/facebook-login/guides/access-tokens/)
- [Graph API Explorer](https://developers.facebook.com/tools/explorer/)

Basic setup:

1. Sign in to [Meta for Developers](https://developers.facebook.com/).
2. Create or select an app for the website.
3. Use Graph API Explorer or the app dashboard to generate a token for a Facebook user who can manage the organization's page.
4. Request Page-read permissions such as `pages_show_list`, `pages_read_engagement`, and, if Meta requires it for the posts endpoint, `pages_read_user_content`.
5. Call `/me/accounts?fields=id,name,access_token` to find the organization's Page ID and Page access token.
6. Test the same endpoint used by the website:

```text
https://graph.facebook.com/v23.0/{PAGE_ID}/posts?fields=id,created_time,message,permalink_url,attachments{title,description,url,unshimmed_url}&limit=4&access_token={PAGE_ACCESS_TOKEN}
```

7. Put the working values into the hosting environment:

```text
FACEBOOK_PAGE_ID=your-page-id
FACEBOOK_PAGE_ACCESS_TOKEN=your-page-access-token
FACEBOOK_GRAPH_API_VERSION=v23.0
FACEBOOK_CACHE_TTL_SECONDS=3600
```

Use Meta's token debugger to check whether the token expires. If it does, refresh it before expiration or follow Meta's current process for creating a longer-lived Page access token.

If the Facebook token is missing, expired, or rejected, the website still renders with cached posts or the static fallback updates in `src/index.php`.

## Facebook Feed Cache

When Facebook API requests succeed, the site writes cached feed data to:

```text
src/cache/facebook-posts.json
```

The `cache/` directory is created automatically when PHP has write permission. If the server cannot write to `src/cache`, the site will still render by using the current request data, existing cache data, or the static fallback updates in `src/index.php`.

## Content Updates

Most editable site content is near the top of `src/index.php`:

- Organization name and donation URL
- Contact email, phone, text number, and address
- Impact statistics
- Static fallback Facebook updates
- Sponsor image map links

Images used by the page are in `src/images/`. If you replace an image, keep the same filename or update the matching `<img>` reference in `src/index.php`.

## Maintenance Notes

- There is no build command; edit PHP, CSS, and image assets directly.
- The CSS is embedded in `src/index.php`.
- The site is intentionally one page. Keep future changes focused on essential nonprofit information.
- Facebook is used as the lightweight event/update source instead of adding a CMS.
- Root-level image files appear to mirror `src/images/`; the running site uses `src/images/`.
- Keep secrets out of Git history before making this repository public.

## License

Free as in beer.

You may use, copy, modify, and deploy this website source at no cost. The source is provided as-is, without warranty.
