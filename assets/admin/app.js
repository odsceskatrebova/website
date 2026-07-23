import { ckeditorConfigRegistry, ckeditorPluginRegistry } from 'sulu-admin-bundle/containers';
import { Highlight } from '@ckeditor/ckeditor5-highlight/src/highlight';
import 'sulu-ai-translator-bundle';
import 'sulu-preview-nav';

ckeditorPluginRegistry.add(Highlight);

ckeditorConfigRegistry.add((config) => ({
    toolbar: [
        'heading',
        'bold', 'italic', 'underline', 'strikethrough',
        'subscript', 'superscript',
        'highlight',
        'bulletedlist', 'numberedlist',
        'externalLink', 'internalLink',
        'alignment', 'insertTable', 'code',
    ],
    highlight: {
        options: [
            { model: 'penPrimary', class: 'pen-primary', title: 'Primary blue', color: '#1B5E8A', type: 'pen' },
            { model: 'penAccent',  class: 'pen-accent',  title: 'Accent teal',  color: '#5BB8D4', type: 'pen' },
        ],
    },
    heading: {
        options: [
            { model: 'paragraph',       view: { name: 'p' },                    class: 'ck-heading_paragraph',        title: 'Paragraph',        converterPriority: 'high' },
            { model: 'paragraphMedium', view: { name: 'p', classes: 'medium' }, class: 'ck-heading_paragraph_medium', title: 'Paragraph medium', converterPriority: 'high' },
            { model: 'paragraphLarge',  view: { name: 'p', classes: 'large' },  class: 'ck-heading_paragraph_large',  title: 'Paragraph large',  converterPriority: 'high' },
            { model: 'paragraphSmall',  view: { name: 'p', classes: 'small' },  class: 'ck-heading_paragraph_small',  title: 'Paragraph small',  converterPriority: 'high' },
            { model: 'paragraphAccent', view: { name: 'p', classes: 'accent' }, class: 'ck-heading_paragraph_accent', title: 'Paragraph accent', converterPriority: 'high' },
            { model: 'heading1', view: 'h1', title: 'H1 — one per page', class: 'ck-heading_heading1' },
            { model: 'heading2', view: 'h2', title: 'H2',                class: 'ck-heading_heading2' },
            { model: 'heading3', view: 'h3', title: 'H3',                class: 'ck-heading_heading3' },
            { model: 'heading4', view: 'h4', title: 'H4',                class: 'ck-heading_heading4' },
            { model: 'heading5', view: 'h5', title: 'H5',                class: 'ck-heading_heading5' },
        ],
    },
}));

import './style.css';
