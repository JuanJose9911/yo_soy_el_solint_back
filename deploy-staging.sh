#/bin/bash
git pull
git add .
git commit -m "$1"
git push origin development
ssh root@dev-srv.coex.com.co 'cd /var/docker-apps/solint-back && git pull origin development && docker logs -f --tail 0 solint_php'
