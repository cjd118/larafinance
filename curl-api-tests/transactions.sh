export $(grep BEARER_TOKEN .env | xargs)


# get transactions
# curl -s localhost:80/api/transactions \
# -H "Authorization: Bearer $BEARER_TOKEN" \
# -H "Accept: application/json" | jq

# get specific transaction
curl -s localhost:80/api/transactions/1 \
-H "Authorization: Bearer $BEARER_TOKEN" \
-H "Accept: application/json" | jq

#create new account
# curl -s -X POST localhost:80/api/accounts \
# -H "Authorization: Bearer $BEARER_TOKEN" \
# -H "Content-Type: application/json" \
# -H "Accept: application/json" \
# -d '{
#     "name": "Test Account",
#     "description": "Test Account Description",
#     "account_category_id": 1
# }' | jq

#delete account
# curl -s -X DELETE localhost:80/api/accounts/5 \
# -H "Authorization: Bearer $BEARER_TOKEN" \
# -H "Accept: application/json" | jq