function woogleDeleteMessages() {
	var expires = (new Date()).toUTCString();
	var newCookie = 'woogle_messages=; expires=' + expires;
	document.cookie = newCookie;
}