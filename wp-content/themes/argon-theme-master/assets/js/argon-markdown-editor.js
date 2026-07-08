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
		var labels = ["B", "I", "#", ">", "-", "1.", "@", "im", "T", "--", "{}", "up", "pv", "2", "[]", "?", "v"];
		var buttons = document.querySelectorAll(".editor-toolbar button");
		for (var i = 0; i < buttons.length; i++) {
			var label = labels[i] || "";
			if (label) {
				buttons[i].textContent = label;
				buttons[i].setAttribute("data-argon-label", label);
			}
		}
	}

	function insertIntoEditor(editor, text) {
		if (!editor || !editor.codemirror) {
			return;
		}
		var cm = editor.codemirror;
		var doc = cm.getDoc();
		var cursor = doc.getCursor();
		doc.replaceRange(text, cursor);
		cm.focus();
	}

	function getUploadSnippet(attachment) {
		var url = attachment.url || "";
		var title = attachment.title || attachment.filename || url;
		var mime = attachment.mime || "";
		var type = attachment.type || "";
		if (!url) {
			return "";
		}
		if (type === "image" || mime.indexOf("image/") === 0) {
			return "![" + title + "](" + url + ")\n";
		}
		if (type === "video" || mime.indexOf("video/") === 0) {
			return '<video controls src="' + url + '"></video>\n';
		}
		return "[" + title + "](" + url + ")\n";
	}

	function openUploader(editor) {
		if (!window.wp || !window.wp.media) {
			window.alert("WordPress media uploader is not available.");
			return;
		}
		var toolbar = editor.codemirror.getWrapperElement().parentNode.querySelector(".editor-toolbar");
		var uploadButton = toolbar ? toolbar.querySelector(".argon-md-upload") : null;
		if (uploadButton) {
			uploadButton.classList.remove("active", "argon-upload-open");
			uploadButton.blur();
		}
		window.setTimeout(function () {
			if (uploadButton) {
				uploadButton.classList.remove("active", "argon-upload-open");
				uploadButton.blur();
			}
		}, 0);
		var frameOptions = {
			title: "\u672c\u5730\u4e0a\u4f20",
			button: {
				text: "\u63d2\u5165\u6587\u7ae0"
			},
			multiple: true
		};

		var frame = window.wp.media(frameOptions);
		frame.on("select", function () {
			var snippets = [];
			frame.state().get("selection").each(function (item) {
				var attachment = item.toJSON();
				var snippet = getUploadSnippet(attachment);
				if (snippet) {
					snippets.push(snippet);
				}
			});
			if (snippets.length) {
				insertIntoEditor(editor, "\n" + snippets.join("") + "\n");
			}
		});
		frame.on("close", function () {
			closeUploadMenu(editor);
		});
		frame.open();
		window.setTimeout(function () {
			if (uploadButton) {
				uploadButton.classList.remove("active", "argon-upload-open");
				uploadButton.blur();
			}
		}, 120);
	}

	function closeUploadMenu(editor) {
		var toolbar = editor && editor.codemirror
			? editor.codemirror.getWrapperElement().parentNode.querySelector(".editor-toolbar")
			: document.querySelector(".editor-toolbar");
		if (!toolbar) {
			return;
		}
		var menu = toolbar.querySelector(".argon-upload-menu");
		var uploadButton = toolbar.querySelector(".argon-md-upload");
		if (menu) {
			menu.remove();
		}
		if (uploadButton) {
			uploadButton.classList.remove("active", "argon-upload-open");
			uploadButton.blur();
		}
		window.setTimeout(function () {
			if (uploadButton) {
				uploadButton.classList.remove("active", "argon-upload-open");
				uploadButton.blur();
			}
		}, 0);
	}

	function toggleToolbar(editor) {
		var toolbar = editor.codemirror.getWrapperElement().parentNode.querySelector(".editor-toolbar");
		if (!toolbar) {
			return;
		}
		toolbar.classList.toggle("argon-toolbar-collapsed");
		var toggle = toolbar.querySelector(".argon-md-collapse");
		if (toggle) {
			var collapsed = toolbar.classList.contains("argon-toolbar-collapsed");
			toggle.textContent = collapsed ? ">" : "v";
			toggle.setAttribute("title", collapsed ? "\u5c55\u5f00\u5feb\u6377\u6309\u94ae" : "\u6536\u8d77\u5feb\u6377\u6309\u94ae");
		}
	}

	function removeOldPreviewControls(oldPreview, oldLimit) {
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
		}
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

	function createHomePreviewField() {
		if (document.body.classList.contains("post-type-page")) {
			return;
		}
		var titleWrap = document.getElementById("titlediv");
		if (!titleWrap || document.getElementById("argon_home_preview_inline")) {
			return;
		}

		var oldPreview = document.querySelector("textarea[name='argon_home_preview']");
		var oldLimit = document.querySelector("input[name='argon_home_preview_limit']");
		var currentValue = oldPreview ? oldPreview.value : "";
		removeOldPreviewControls(oldPreview, oldLimit);

		var wrap = document.createElement("div");
		wrap.className = "argon-home-preview-inline";
		wrap.innerHTML =
			'<label for="argon_home_preview_inline">\u9996\u9875\u5c55\u793a\u6458\u8981</label>' +
			'<textarea name="argon_home_preview" id="argon_home_preview_inline" rows="3" placeholder="\u8fd9\u91cc\u586b\u5199\u7684\u5185\u5bb9\u4f1a\u5c55\u793a\u5728\u9996\u9875\u6587\u7ae0\u5361\u7247\u91cc\uff1b\u7559\u7a7a\u5219\u9996\u9875\u4e0d\u663e\u793a\u6458\u8981\u3002"></textarea>';
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
			"<strong>Markdown \u5199\u4f5c\u6a21\u5f0f</strong>" +
			"<span>\u6807\u9898\u7528 <code>#</code>\uff0c\u4ee3\u7801\u5757\u7528 <code>```</code>\uff0c\u884c\u5185\u516c\u5f0f <code>$E=mc^2$</code>\uff0c\u5757\u516c\u5f0f <code>$$...$$</code></span>";
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
				"code",
				{
					name: "argon-upload",
					action: function () {
						openUploader(markdownEditor);
					},
					className: "argon-md-upload",
					title: "\u672c\u5730\u4e0a\u4f20"
				},
				"preview", "side-by-side", "fullscreen", "|",
				"guide",
				{
					name: "collapse-toolbar",
					action: function () {
						toggleToolbar(markdownEditor);
					},
					className: "argon-md-collapse",
					title: "\u6536\u8d77\u5feb\u6377\u6309\u94ae"
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
			collapseButton.textContent = "v";
		}

		window.argonMarkdownEditor = markdownEditor;
	}

	if (document.readyState === "loading") {
		document.addEventListener("DOMContentLoaded", initMarkdownEditor);
	} else {
		initMarkdownEditor();
	}
})();
