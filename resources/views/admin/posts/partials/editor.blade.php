{{--
    Rich text editor.

    Built on contenteditable rather than a bundled library so the admin has no
    extra npm dependency to install or deploy. Produces plain semantic HTML,
    which is what the public post template renders.
--}}
<div class="rte" x-data="richText(@js(old('content', $post->content ?? '')))" x-init="init()">

    <div class="rte__toolbar" role="toolbar" aria-label="Formatting">
        <div class="rte__group">
            <button type="button" class="rte__btn" @click="cmd('bold')" title="Bold (Ctrl+B)"><i class="fa-solid fa-bold"></i></button>
            <button type="button" class="rte__btn" @click="cmd('italic')" title="Italic (Ctrl+I)"><i class="fa-solid fa-italic"></i></button>
            <button type="button" class="rte__btn" @click="cmd('underline')" title="Underline (Ctrl+U)"><i class="fa-solid fa-underline"></i></button>
            <button type="button" class="rte__btn" @click="cmd('strikeThrough')" title="Strikethrough"><i class="fa-solid fa-strikethrough"></i></button>
        </div>

        <div class="rte__group">
            <button type="button" class="rte__btn rte__btn--text" @click="block('H2')" title="Heading 2">H2</button>
            <button type="button" class="rte__btn rte__btn--text" @click="block('H3')" title="Heading 3">H3</button>
            <button type="button" class="rte__btn rte__btn--text" @click="block('H4')" title="Heading 4">H4</button>
            <button type="button" class="rte__btn rte__btn--text" @click="block('P')" title="Paragraph">P</button>
        </div>

        <div class="rte__group">
            <button type="button" class="rte__btn" @click="cmd('insertUnorderedList')" title="Bullet list"><i class="fa-solid fa-list-ul"></i></button>
            <button type="button" class="rte__btn" @click="cmd('insertOrderedList')" title="Numbered list"><i class="fa-solid fa-list-ol"></i></button>
            <button type="button" class="rte__btn" @click="block('BLOCKQUOTE')" title="Quote"><i class="fa-solid fa-quote-left"></i></button>
            <button type="button" class="rte__btn" @click="codeBlock()" title="Code block"><i class="fa-solid fa-code"></i></button>
        </div>

        <div class="rte__group">
            <button type="button" class="rte__btn" @click="cmd('justifyLeft')" title="Align left"><i class="fa-solid fa-align-left"></i></button>
            <button type="button" class="rte__btn" @click="cmd('justifyCenter')" title="Align centre"><i class="fa-solid fa-align-center"></i></button>
            <button type="button" class="rte__btn" @click="cmd('justifyRight')" title="Align right"><i class="fa-solid fa-align-right"></i></button>
        </div>

        <div class="rte__group">
            <button type="button" class="rte__btn" @click="addLink()" title="Insert link"><i class="fa-solid fa-link"></i></button>
            <button type="button" class="rte__btn" @click="cmd('unlink')" title="Remove link"><i class="fa-solid fa-link-slash"></i></button>
            <button type="button" class="rte__btn" @click="addImage()" title="Insert image"><i class="fa-solid fa-image"></i></button>
            <button type="button" class="rte__btn" @click="cmd('insertHorizontalRule')" title="Divider"><i class="fa-solid fa-minus"></i></button>
        </div>

        <div class="rte__group">
            <label class="rte__btn" title="Text colour" style="position:relative; overflow:hidden;">
                <i class="fa-solid fa-palette"></i>
                <input type="color" @change="cmd('foreColor', $event.target.value)"
                       style="position:absolute; inset:0; opacity:0; cursor:pointer;">
            </label>
        </div>

        <div class="spacer"></div>

        <div class="rte__group">
            <button type="button" class="rte__btn" @click="cmd('undo')" title="Undo"><i class="fa-solid fa-rotate-left"></i></button>
            <button type="button" class="rte__btn" @click="cmd('redo')" title="Redo"><i class="fa-solid fa-rotate-right"></i></button>
        </div>
    </div>

    <div class="rte__split">
        <div class="rte__surface prose-dock"
             contenteditable="true"
             x-ref="editor"
             @input="sync()"
             @blur="sync()"
             role="textbox"
             aria-multiline="true"
             aria-label="Post content"></div>

        {{-- Live document outline, built from the headings as they are typed --}}
        <aside class="rte__outline" aria-label="Document outline">
            <div class="rte__outline-head">Outline</div>
            <template x-if="!outline.length">
                <p class="rte__outline-empty">Add H2–H4 headings to build an outline.</p>
            </template>
            <ul class="rte__outline-list">
                <template x-for="(h, i) in outline" :key="i">
                    <li :class="`rte__outline-item rte__outline-item--${h.level}`">
                        <button type="button" @click="jumpTo(h.id)" x-text="h.text"></button>
                    </li>
                </template>
            </ul>
        </aside>
    </div>

    <input type="hidden" name="content" :value="html">

    <div class="rte__status">
        <span x-text="`${words} words · ${minutes} min read`"></span>
    </div>
</div>

@error('content')
    <div class="field__error" style="margin-top:6px;">
        <i class="fa-solid fa-circle-exclamation" style="margin-top:1px;"></i>
        <span>{{ $message }}</span>
    </div>
@enderror
