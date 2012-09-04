
function s3_insertImage(imgURL, title) {
	if (!title) title = '';
    return s3_insert('<img src="'+imgURL+'" class="s3-img" border="0" alt="'+title+'" /> ');
}

function s3_insertLink(label, url) {
    var useBittorrent = document.getElementById('useBittorrent').checked
    return s3_insert('<a href="'+url+(useBittorrent ? '?torrent' : '')+'" class="s3-link'+(useBittorrent ? ' torrent' : '')+'">' + label + '</a> ');
}
function s3_insert(h) {
    var win = window.dialogArguments || opener || parent || top;
	
	if (typeof win.send_to_editor == 'function') {
		win.send_to_editor(h);
		if (typeof win.tb_remove == 'function') 
			win.tb_remove();
		return false;
	}
	tinyMCE = win.tinyMCE;
	if ( typeof tinyMCE != 'undefined' && tinyMCE.getInstanceById('content') ) {
		tinyMCE.selectedInstance.getWin().focus();
		tinyMCE.execCommand('mceInsertContent', false, h);
	} else win.edInsertContent(win.edCanvas, h);

	return false;
}
function s3_toggleUpload() {
	document.getElementById('create-form').style.display='none';
	
	var div = document.getElementById('upload-form');
    if (div.style.display == 'block') {
		div.style.display = 'none';
	} else {
		div.style.display = 'block';
	}
	return false;
}
function s3_toggleCreateFolder() {
	document.getElementById('upload-form').style.display='none';
	
	var div = document.getElementById('create-form');
	if (div.style.display == 'block') {
		div.style.display = 'none';
	} else {
		div.style.display = 'block';
		document.getElementById('newfolder').focus();
	}
	return false;
    

	var div = document.getElementById('createFolder');
	if (div.className != 'create') {
		div.className = 'create';
		document.getElementById('newfolder').focus();
	} else {
		div.className = '';
	}
}
