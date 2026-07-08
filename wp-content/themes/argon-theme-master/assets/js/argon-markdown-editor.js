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

	function isEscaped(text, position) {
		var slashes = 0;
		for (var i = position - 1; i >= 0 && text.charAt(i) === "\\"; i--) {
			slashes++;
		}
		return slashes % 2 === 1;
	}

	function findUnescapedDelimiter(text, delimiter, offset, singleDollar) {
		while (offset < text.length) {
			var position = text.indexOf(delimiter, offset);
			if (position === -1) {
				return -1;
			}
			if (!isEscaped(text, position)) {
				if (!singleDollar) {
					return position;
				}
				var previous = position > 0 ? text.charAt(position - 1) : "";
				var next = position + 1 < text.length ? text.charAt(position + 1) : "";
				if (previous !== "$" && next !== "$") {
					return position;
				}
			}
			offset = position + delimiter.length;
		}
		return -1;
	}

	function protectMath(text) {
		var blocks = [];
		var result = "";
		var i = 0;
		function store(block) {
			var key = "ARGONMARKDOWNMATH" + blocks.length + "TOKEN";
			blocks.push({ key: key, value: block });
			return key;
		}
		while (i < text.length) {
			if (text.substr(i, 2) === "$$" && !isEscaped(text, i)) {
				var displayEnd = findUnescapedDelimiter(text, "$$", i + 2, false);
				if (displayEnd !== -1) {
					result += store(text.slice(i, displayEnd + 2));
					i = displayEnd + 2;
					continue;
				}
			}
			if (text.substr(i, 2) === "\\[") {
				var bracketEnd = text.indexOf("\\]", i + 2);
				if (bracketEnd !== -1) {
					result += store(text.slice(i, bracketEnd + 2));
					i = bracketEnd + 2;
					continue;
				}
			}
			if (text.substr(i, 2) === "\\(") {
				var parenEnd = text.indexOf("\\)", i + 2);
				if (parenEnd !== -1) {
					result += store(text.slice(i, parenEnd + 2));
					i = parenEnd + 2;
					continue;
				}
			}
			if (text.charAt(i) === "$" && !isEscaped(text, i) && text.substr(i, 2) !== "$$") {
				var inlineEnd = findUnescapedDelimiter(text, "$", i + 1, true);
				if (inlineEnd !== -1) {
					var inside = text.slice(i + 1, inlineEnd);
					if (inside.trim() !== "") {
						result += store(text.slice(i, inlineEnd + 1));
						i = inlineEnd + 1;
						continue;
					}
				}
			}
			result += text.charAt(i);
			i++;
		}
		return { text: result, blocks: blocks };
	}

	function restoreMath(html, blocks) {
		for (var i = 0; i < blocks.length; i++) {
			html = html.split(blocks[i].key).join(blocks[i].value);
		}
		return html;
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
		var oldPreview = document.querySelector("textarea[name='argon_home_preview']");
		var oldLimit = document.querySelector("input[name='argon_home_preview_limit']");
		var inlinePreview = document.getElementById("argon_home_preview_inline");
		if (inlinePreview) {
			var inlineWrap = inlinePreview.closest(".argon-home-preview-inline");
			if (inlineWrap) {
				inlineWrap.remove();
			}
		}
		removeOldPreviewControls(oldPreview, oldLimit);
	}

	function isCompositePageEditor() {
		var mode = document.getElementById("argon_page_mode");
		return document.body.classList.contains("post-type-page") && mode && mode.value === "composite";
	}

	function syncCompositePageEditor() {
		var mode = document.getElementById("argon_page_mode");
		if (!document.body.classList.contains("post-type-page") || !mode) {
			return false;
		}
		var isComposite = mode.value === "composite";
		document.body.classList.toggle("argon-composite-page-editor", isComposite);
		return isComposite;
	}

	function bindCompositePageModeWatcher() {
		var mode = document.getElementById("argon_page_mode");
		if (!mode || mode.dataset.argonCompositeWatcherReady) {
			return;
		}
		mode.dataset.argonCompositeWatcherReady = "true";
		mode.addEventListener("change", function () {
			var isComposite = syncCompositePageEditor();
			if (!isComposite) {
				window.setTimeout(initMarkdownEditor, 0);
			}
		});
	}

	function syncCompositeBannerPreview(url) {
		var imagePreview = document.getElementById("argon_composite_banner_preview");
		if (!imagePreview) {
			return;
		}
		var image = imagePreview.querySelector("img");
		if (url && image) {
			image.src = url;
			imagePreview.style.display = "";
			return;
		}
		if (image) {
			image.removeAttribute("src");
		}
		imagePreview.style.display = "none";
	}

	function openCompositeBannerMediaFrame() {
		var imageInput = document.getElementById("argon_composite_banner_background");
		if (!imageInput) {
			return;
		}
		if (!window.wp || !window.wp.media) {
			window.alert("\u5a92\u4f53\u5e93\u8fd8\u6ca1\u6709\u52a0\u8f7d\u5b8c\uff0c\u8bf7\u7b49\u4e00\u4e0b\u518d\u70b9\u3002");
			return;
		}
		var frame = window.wp.media({
			title: "\u4e0a\u4f20\u6216\u9009\u62e9\u9876\u90e8 Banner \u56fe\u7247",
			button: {
				text: "\u4f7f\u7528\u8fd9\u5f20\u56fe\u7247"
			},
			library: {
				type: "image"
			},
			multiple: false
		});
		frame.on("select", function () {
			var attachment = frame.state().get("selection").first().toJSON();
			imageInput.value = attachment.url || "";
			syncCompositeBannerPreview(imageInput.value);
			imageInput.dispatchEvent(new Event("change", { bubbles: true }));
		});
		frame.open();
	}

	function bindCompositeBannerUploader() {
		if (document.body.dataset.argonCompositeUploaderReady) {
			return;
		}
		document.body.dataset.argonCompositeUploaderReady = "true";
		document.addEventListener("click", function (event) {
			var uploadButton = event.target.closest(".argon-composite-page-image-select");
			if (uploadButton) {
				event.preventDefault();
				event.stopPropagation();
				openCompositeBannerMediaFrame();
				return;
			}
			var clearButton = event.target.closest(".argon-composite-page-image-clear");
			if (clearButton) {
				event.preventDefault();
				event.stopPropagation();
				var imageInput = document.getElementById("argon_composite_banner_background");
				if (imageInput) {
					imageInput.value = "";
					imageInput.dispatchEvent(new Event("change", { bubbles: true }));
				}
				syncCompositeBannerPreview("");
			}
		});
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
		bindCompositeBannerUploader();
		bindCompositePageModeWatcher();
		if (syncCompositePageEditor() || isCompositePageEditor()) {
			return;
		}

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
				var protectedMath = protectMath(plainText);
				var html = markdownEditor && typeof markdownEditor.markdown === "function"
					? markdownEditor.markdown(protectedMath.text)
					: protectedMath.text;
				html = restoreMath(html, protectedMath.blocks);
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
