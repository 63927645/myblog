(function () {
	"use strict";

	function typesetMath(container) {
		if (window.MathJax && typeof window.MathJax.typesetPromise === "function") {
			window.MathJax.typesetPromise([container]).catch(function () {});
		}
	}

	function refreshMathPreview() {
		window.setTimeout(function () {
			var previews = document.querySelectorAll(".editor-preview-active, .editor-preview-side");
			for (var i = 0; i < previews.length; i++) {
				typesetMath(previews[i]);
			}
		}, 80);
	}

	function insertHelper(editor) {
		var wrapper = editor.codemirror.getWrapperElement();
		if (!wrapper || document.querySelector(".argon-markdown-helper")) {
			return;
		}
		var helper = document.createElement("div");
		helper.className = "argon-markdown-helper";
		helper.innerHTML =
			"<strong>Markdown 写作模式</strong>" +
			"<span>标题用 <code>#</code>，代码块用 <code>```</code>，行内公式 <code>$E=mc^2$</code>，块公式 <code>$$...$$</code></span>";
		wrapper.parentNode.insertBefore(helper, wrapper);
	}

	function initMarkdownEditor() {
		var textarea = document.getElementById("content");
		if (!textarea || textarea.dataset.argonMarkdownReady || typeof window.EasyMDE === "undefined") {
			return;
		}
		textarea.dataset.argonMarkdownReady = "true";
		document.body.classList.add("argon-markdown-editor-enabled");

		var markdownEditor = null;
		markdownEditor = new window.EasyMDE({
			element: textarea,
			autoDownloadFontAwesome: false,
			spellChecker: false,
			nativeSpellcheck: true,
			autofocus: false,
			indentWithTabs: false,
			lineWrapping: true,
			minHeight: "560px",
			promptURLs: true,
			status: ["lines", "words", "cursor"],
			toolbar: [
				"bold", "italic", "heading", "|",
				"quote", "unordered-list", "ordered-list", "|",
				"link", "image", "table", "horizontal-rule", "|",
				"code", "preview", "side-by-side", "fullscreen", "|",
				"guide"
			],
			renderingConfig: {
				singleLineBreaks: false,
				codeSyntaxHighlighting: false
			},
			previewRender: function (plainText) {
				var html = markdownEditor && typeof markdownEditor.markdown === "function"
					? markdownEditor.markdown(plainText)
					: plainText;
				refreshMathPreview();
				return html;
			}
		});

		markdownEditor.codemirror.on("change", refreshMathPreview);
		insertHelper(markdownEditor);
		window.argonMarkdownEditor = markdownEditor;
	}

	if (document.readyState === "loading") {
		document.addEventListener("DOMContentLoaded", initMarkdownEditor);
	} else {
		initMarkdownEditor();
	}
})();
