export $(grep BEARER_TOKEN .env | xargs)

curl -s localhost:80/api/account-categories  \
-H "Authorization: Bearer $BEARER_TOKEN" \
-H "Accept: application/json" | jq