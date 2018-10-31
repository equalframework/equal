# resiway
Social platform for sharing practical information about Self-Sufficiency, Transition and Permaculture
It aims to be as resilient as possible.

This project includes 
* ResiExchange : a Q&A application 
* ResiLib : a document sharing application 


### Configuration

#### PHP.ini
Following constants must be defined to a custom value, matching application requirements:  

* `post_max_size`
* `upload_max_filesize`

#### PHP modules
json
xml
gd
gmp
mbstring
mysql



### API usage 

(In order to test the API an its features, you may use the excellent https://www.hurl.it/)

#### Methods 
At the moment, only GET method is supported.


#### URLS
* documents : https://www.resiway.org/api/documents
* questions : https://www.resiway.org/api/questions
* global : https://www.resiway.org/api/search


#### Parameters
* `api` : API version (by default '1.0', which uses JSON API 1.0 specs http://jsonapi.org/format/)
* `limit`: number of objects to return in one query (min.5, max. 100)
* `start`: position of the first object to return


#### Return values
Document JSON API (RFC7159) : http://jsonapi.org/format/  

Content-Type: application/vnd.api+json  

Data structure :  

    {
        "jsonapi": {
            "version": "1.0"
        },
        "meta": {
            "total-pages": 1
        },
        "data": [ {
            "type": "document",
            "id": "1",
            "attributes": {

            },
            "relationships": {

            }
        } ]
    }