function transform() {
    var url = document.getElementById("url").value; //getting URL from text field
    var pattern = /id(.+)\?/; //regex pattern
	var results = pattern.exec(url); //results of regex
	var id = results[1]; //results[1] holds the ID
	var idField = document.getElementById("id"); //getting ID text field
	idField.value = id; //setting ID text field to results[1] from regexd
}
