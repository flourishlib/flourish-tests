var foobar=123;var func=function(){alert('hi! /* */ //');}()
var regEx=/\/* this is not a comment even though it looks like it! *\///*! This is a special comment
 *  nothing should be changed in here
 */var multiLineString="\
This is a multi-line string\
With embedded inline comments! // like this\
And even a multi-line comment! /* \
This is not a comment!\
*/\
";