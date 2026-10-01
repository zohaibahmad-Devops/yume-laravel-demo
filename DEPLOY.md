# Deploying this demo

Two steps, both on accounts only you can sign into.

## 1. Create the GitHub repository

https://github.com/new

- Owner: `zohaibahmad-Devops`
- Name: `yume-laravel-demo`
- Private or public — either works
- **Do not** add a README, .gitignore or licence; this repo already has them

Then, from `C:\Zohaib\yume-laravel-demo`:

```
git push -u origin main
```

The remote is already set, so that is the whole command.

## 2. Create the Render service

https://dashboard.render.com/select-repo?type=web

- Connect the GitHub account if Render asks
- Pick `yume-laravel-demo`
- Render reads `render.yaml`, so Language/Build/Start are already decided —
  leave them alone
- Instance type: **Free**
- Create Web Service

The first build takes roughly 4–6 minutes: it installs Composer dependencies
inside the container. The URL will be `https://yume-inventory-demo.onrender.com`
unless that name is taken, in which case Render appends a suffix — use whatever
it shows you.

## If the first build fails

Open the service's **Logs** tab, copy the error, and send it over. A first
Docker build failing on a missing PHP extension or a path is ordinary; the log
says exactly which.

## Known free-tier behaviour

Render spins a free service down after 15 minutes of no traffic, so the first
visit after a quiet spell takes 30–50 seconds to wake. Worth saying in advance
if you are sending the link to a client, otherwise it reads as a slow app.
