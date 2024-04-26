http://10.16.16.245/m2_232/graphql
Set endpoint
{
    products(
      filter:{
        category_id: {eq: "23"},
        style_general:{eq:"117"},
        color:{eq:"49"}
      }
      currentPage: 1
      pageSize: 5
    ){
    total_count
    items{
      name
    }
    filters{
      name
      request_var
      filter_items_count
      filter_items{
        items_count
        label
        value_string
      }
    }
  }
}
{
  "errors": [
    {
      "message": "Field \"style_general\" is not defined by type ProductFilterInput.",
      "category": "graphql",
      "locations": [
        {
          "line": 5,
          "column": 9
        }
      ]
    },
    {
      "message": "Field \"color\" is not defined by type ProductFilterInput.",
      "category": "graphql",
      "locations": [
        {
          "line": 6,
          "column": 9
        }
      ]
    }
  ]
}
Documentation Explorer
Search Schema...
A GraphQL schema provides a root type for each kind of operation.

query: Query
mutation: Mutation