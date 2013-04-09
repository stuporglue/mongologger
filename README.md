# MongoLogger #

MongoLogger is a PHP shim that pretends to be a MongoDB connection. It holds a real MongoDB connection inside it and gives you a place to do MongoDB logging or debugging. 

## Why Is MongoLogger Useful ##

When developing with MySQL I often have an object or function which handles all my queries. This gives me a single place to handle logging, sql error handling and debugging (Using things like debug_print_backtrace or xdebug).

With MongoDB's magic this isn't quite as simple, so a simple shim is used to stand inbetween your code and MongoDB. You continue to make your MongoDB calls as normal, but now they get passed through MongoLogger before going on to the MongoDB object itself. This gives you an opportunity to do the debugging and logging that you'd like.

## Usage ##

During development I replace the $mongo object with my MongoLogger object, like so:

```
$mongodb = new Mongo("mongodb://un:pw@host/db"); // Connect to Mongo
$mongo = $mongodb->db;                           // pick your database
$mongo = new MongoLogger($mongo);                // Replace the $mongo
```

**Note:** Magic Methods, and call_user_func_array in particular, are slow. Not recommended for production use!
