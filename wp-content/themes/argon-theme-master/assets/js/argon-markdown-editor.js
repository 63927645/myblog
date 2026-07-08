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

	function setToolbarLabels() {
		var titleLabels = {
			"Bold": "B",
			"Italic": "I",
			"Heading": "#",
			"Quote": ">",
			"Generic List": "-",
			"Numbered List": "1.",
			"Create Link": "link",
			"Insert Image": "img",
			"Insert Table": "table",
			"Insert Horizontal Line": "--",
			"Code": "{}",
			"Toggle Preview": "eye",
			"Toggle Side by Side": "split",
			"Toggle Fullscreen": "full",
			"Markdown Guide": "?"
		};
		var classLabels = {
			"fa-bold": "B",
			"fa-italic": "I",
			"fa-header": "#",
			"fa-quote-left": ">",
			"fa-list-ul": "-",
			"fa-list-ol": "1.",
			"fa-link": "link",
			"fa-picture-o": "img",
			"fa-table": "table",
			"fa-minus": "--",
			"fa-code": "{}",
			"fa-eye": "eye",
			"fa-columns": "split",
			"fa-arrows-alt": "full",
			"fa-question-circle": "?"
		};
		var buttons = document.querySelectorAll(".editor-toolbar button");
		for (var i = 0; i < buttons.length; i++) {
			var title = buttons[i].getAttribute("title");
			var label = titleLabels[title] || "";
			if (!label) {
				for (var className in classLabels) {
					if (Object.prototype.hasOwnProperty.call(classLabels, className) && buttons[i].classList.contains(className)) {
						label = classLabels[className];
						break;
					}
				}
			}
			if (label) {
				buttons[i].textContent = label;
				buttons[i].setAttribute("aria-label", title);
			}
		}
	}

	function toggleToolbar(editor) {
		var toolbar = editor.codemirror.getWrapperElement().parentNode.querySelector(".editor-toolbar");
		if (!toolbar) {
			return;
		}
		toolbar.classList.toggle("argon-toolbar-collapsed");
		var toggle = toolbar.querySelector(".argon-md-collapse");
		if (toggle) {
			toggle.textContent = toolbar.classList.contains("argon-toolbar-collapsed") ? "▸" : "▾";
			toggle.setAttribute("title", toolbar.classList.contains("argon-toolbar-collapsed") ? "展开快捷按钮" : "收起快捷按钮");
		}
	}

	function createHomePreviewField() {
		var titleWrap = document.getElementById("titlediv");
		if (!titleWrap || document.getElementById("argon_home_preview_inline")) {
			return;
		}

		var oldPreview = document.querySelector("textarea[name='argon_home_preview']");
		var oldLimit = document.querySelector("input[name='argon_home_preview_limit']");
		var currentValue = oldPreview ? oldPreview.value : "";

		if (oldPreview) {
			var previewTitle = oldPreview.previousElementSibling;
			var previewNote = oldPreview.nextElementSibling;
			if (previewTitle && previewTitle.tagName === "H4") {
				previewTitle.remove();
			}
			if (previewNote && previewNote.tagName === "P") {
				previewNote.remove();
			}
			oldPreview.remove();
			if (oldLimit) {
				var limitTitle = oldLimit.previousElementSibling;
				var limitNote = oldLimit.nextElementSibling;
				if (limitTitle && limitTitle.tagName === "H4") {
					limitTitle.remove();
				}
				if (limitNote && limitNote.tagName === "P") {
					limitNote.remove();
				}
				oldLimit.remove();
			}
		}

		var wrap = document.createElement("div");
		wrap.className = "argon-home-preview-inline";
		wrap.innerHTML =
			'<label for="argon_home_preview_inline">首页展示摘要</label>' +
			'<textarea name="argon_home_preview" id="argon_home_preview_inline" rows="3" placeholder="这里填写的内容会展示在首页文章卡片里；留空则首页不显示摘要。"></textarea>';
		titleWrap.parentNode.insertBefore(wrap, titleWrap.nextSibling);
		document.getElementById("argon_home_preview_inline").value = currentValue;
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
		createHomePreviewField();

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
				"guide",
				{
					name: "collapse-toolbar",
					action: function () {
						toggleToolbar(markdownEditor);
					},
					className: "argon-md-collapse",
					title: "收起快捷按钮"
				}
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
		setToolbarLabels();

		var collapseButton = document.querySelector(".editor-toolbar .argon-md-collapse");
		if (collapseButton) {
			collapseButton.textContent = "▾";
		}

		window.argonMarkdownEditor = markdownEditor;
	}

	if (document.readyState === "loading") {
		document.addEventListener("DOMContentLoaded", initMarkdownEditor);
	} else {
		initMarkdownEditor();
	}
})();
