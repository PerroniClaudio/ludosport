curl \
  -X PATCH 'http://localhost:7700/experimental-features/' \
  -H 'Content-Type: application/json' \
  -H 'Authorization: Bearer KJIXoLcyu4ZHbApL8UjabvDuqqlUvsCvR4vxP7ge0DE' \
  --data-binary '{
    "metrics": true
  }'