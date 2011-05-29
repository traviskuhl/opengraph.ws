# OpenGraph.ws

## Features

* Resource URLs normalized by the API
* Page & Domain results returned for each request
* Structured response provides easy access to data 
* Data cached for 24 hours for quick API responses
* Fallback to `<title>`, `<meta name='description'>` & `<link rel='image_src'>` if OG tags are not present
* Include all image tags on the page (append `?images=true`)

## API Request
	http://opensource.ws/api/v1?url={:url}
	http://opensource.ws/?q={:url} (need to send with "accept:application/javascript" header)
	http://opensource.ws/api/v1?url={:url}&images={:true|false}
	
## API Response
```javascript
{
    "status": 1,
    "page": {
        "uid": "1d33acfa7bd33ae689546cd3c105f989",
        "resource": "dev.opengraph.ws\\/test",
        "created": "2011-05-21T05:32:49+00:00",
        "meta": {
            "site_name": "IMDb",
            "title": "The Rock",
            "description": "Sean Connery found fame and fortune as the suave, sophisticated British agent, James Bond.",
            "type": "actor",
            "image": "http:\\/\\/ia.media-imdb.com\\/images\\/rock.jpg",
            "url": "http:\\/\\/www.imdb.com\\/title\\/tt0117500\\/"
        },
        "location": {
            "latitude": "37.416343",
            "longitude": "-122.153013",
            "locality": "Palo Alto",
            "region": "CA"
        },
        "contact": {
            "email": "me@example.com",
            "phone_number": "650-123-4567",
            "fax_number": "+1-415-123-4567"
        },
        "video": {
            "video": "http:\\/\\/example.com\\/awesome.flv",
            "height": "640",
            "width": "385",
            "type": "application\\/x-shockwave-flash"
        }
    },
    "domain": {
        "uid": "acce3c70e613974fd3cabc9341b3458d",
        "resource": "dev.opengraph.ws",
        "created": "2011-05-21T05:41:55+00:00",
        "meta": {
            "site_name": "OpenGraph.ws",
            "title": "OpenGraph.ws",
            "description": "A simple web service to return Open Graph meta data.",
            "type": "website",
            "url": "http:\\/\\/opengraph.ws"
        }
    }
}
```