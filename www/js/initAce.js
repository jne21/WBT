function initAce(form) {
	var editors = [];
	$(form).bind (
		'submit', function () {
			$(editors).each(
				function () {
					this.input.value = this.editor.getValue();
				}
			);
		}
	);
//	$('#"+$(form).attr('id')+' .editor').each(
	$(form).find('.editor').each(
		function () {
			var editor = ace.edit($(this).attr("id"));
			editor.getSession().setMode("ace/mode/html");
			var input=document.createElement("input");
			$(input).attr("name", $(this).attr("id")).attr("type", "hidden");
			this.parentNode.appendChild(input);
			editors.push({input:input, editor:editor});
		}
	);
}
