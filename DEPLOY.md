# Deploying this demo

It runs as a Hugging Face Space using the Docker SDK — free, no card required,
and the free CPU tier is more generous than Render's.

## 1. Create the Space

https://huggingface.co/new-space

- Owner: your account
- Space name: `yume-inventory-demo`
- License: MIT
- SDK: **Docker** → **Blank**
- Hardware: **CPU basic · free**
- Visibility: Public

The URL will be `https://<your-username>-yume-inventory-demo.hf.space`.

## 2. Push this repository to it

The Space is itself a git repository. Add it as a second remote and push:

```
git remote add space https://huggingface.co/spaces/<your-username>/yume-inventory-demo
git push space main
```

Git will ask for credentials. Username is your Hugging Face username; the
password is a **write access token**, created at
https://huggingface.co/settings/tokens — not your account password.

GitHub stays the primary remote, so `git push` alone still goes to GitHub.
Pushing to the Space is `git push space main`.

## 3. Watch the build

The Space's **Logs** tab shows the Docker build. It takes roughly 4–6 minutes
the first time, mostly `composer install`.

## How the container is configured

`README.md` carries the Space config in its YAML header — `sdk: docker` and
`app_port: 7860`, which is the port the Dockerfile and `docker/start.sh` both
listen on. Changing one without the other will produce a Space that builds and
then never answers.

## If the first build fails

Open the **Logs** tab, copy the error, and send it over. A first Docker build
failing on a missing PHP extension or a path is ordinary; the log names it.

## Behaviour on the free tier

A free Space pauses after about 48 hours with no visitors and wakes on the next
request. That is far less intrusive than Render's 15-minute spin-down, which is
why the demo lives here.
