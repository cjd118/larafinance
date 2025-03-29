export $(grep BEARER_TOKEN .env | xargs)

# get transaction categories
curl -s localhost:80/api/transaction-categories  \
-H "Authorization: Bearer $BEARER_TOKEN" \
-H "Accept: application/json" | jq

# get specific transaction categories
# curl -s localhost:80/api/transaction-categories/1  \
# -H "Authorization: Bearer $BEARER_TOKEN" \
# -H "Accept: application/json" | jq

#create new transaction categories
# curl -s -X POST localhost:80/api/transaction-categories \
# -H "Authorization: Bearer $BEARER_TOKEN" \
# -H "Content-Type: application/json" \
# -H "Accept: application/json" \
# -d '{
#     "name": "Test Category",
#     "parent_id": 1
# }' | jq

#update transaction category
# curl -s -X PUT localhost:80/api/transaction-categories/2 \
# -H "Authorization: Bearer $BEARER_TOKEN" \
# -H "Content-Type: application/json" \
# -H "Accept: application/json" \
# -d '{
#     "name": "Updated Category Name",
#     "parent_id": 3
# }' | jq

#delete transaction categories
# curl -s -X DELETE localhost:80/api/transaction-categories/12 \
# -H "Authorization: Bearer $BEARER_TOKEN" \
# -H "Accept: application/json" | jq