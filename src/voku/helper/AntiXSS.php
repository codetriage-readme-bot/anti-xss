<?php

declare(strict_types=1);

namespace voku\helper;

use const ENT_DISALLOWED;
use const ENT_HTML5;
use const ENT_QUOTES;
use const ENT_SUBSTITUTE;
use const HTML_ENTITIES;

/**
 * AntiXSS
 *
 * ported from "CodeIgniter"
 *
 * @copyright   Copyright (c) 2008 - 2014, EllisLab, Inc. (http://ellislab.com/)
 * @copyright   Copyright (c) 2014 - 2015, British Columbia Institute of Technology (http://bcit.ca/)
 * @copyright   Copyright (c) 2015 - 2019, Lars Moelleken (https://moelleken.org/)
 * @license     http://opensource.org/licenses/MIT	MIT License
 */
final class AntiXSS
{
    /**
     * List of never allowed regex replacements.
     *
     * @var array
     */
    private static $_never_allowed_regex = [
        // default javascript
        '(\(?document\)?|\(?window\)?(\.document)?)\.(location|on\w*)',
        // data-attribute + base64
        "([\"'])?data\s*:[^\\1]*?base64[^\\1]*?,[^\\1]*?\\1?",
        // remove Netscape 4 JS entities
        '&\s*\{[^}]*(\}\s*;?|$)',
        // old IE, old Netscape
        'expression\s*(\(|&\#40;)',
    ];

    /**
     * List of never allowed call statements.
     *
     * @var array
     */
    private static $_never_allowed_call = [
        // default javascript
        'javascript',
        // Java: jar-protocol is an XSS hazard
        'jar',
        // Mac (will not run the script, but open it in AppleScript Editor)
        'applescript',
        // IE: https://www.owasp.org/index.php/XSS_Filter_Evasion_Cheat_Sheet#VBscript_in_an_image
        'vbscript',
        'vbs',
        // IE, surprise!
        'wscript',
        // IE
        'jscript',
        // https://html5sec.org/#behavior
        'behavior',
        // old Netscape
        'mocha',
        // old Netscape
        'livescript',
        // default view source
        'view-source',
    ];

    /**
     * List of never allowed strings, afterwards.
     *
     * @var array
     */
    private static $_never_allowed_on_events_afterwards = [
        'onAbort',
        'onActivate',
        'onAttribute',
        'onAfterPrint',
        'onAfterScriptExecute',
        'onAfterUpdate',
        'onAnimationCancel',
        'onAnimationEnd',
        'onAnimationIteration',
        'onAnimationStart',
        'onAriaRequest',
        'onAutoComplete',
        'onAutoCompleteError',
        'onAuxClick',
        'onBeforeActivate',
        'onBeforeCopy',
        'onBeforeCut',
        'onBeforeDeactivate',
        'onBeforeEditFocus',
        'onBeforePaste',
        'onBeforePrint',
        'onBeforeScriptExecute',
        'onBeforeUnload',
        'onBeforeUpdate',
        'onBegin',
        'onBlur',
        'onBounce',
        'onCancel',
        'onCanPlay',
        'onCanPlayThrough',
        'onCellChange',
        'onChange',
        'onClick',
        'onClose',
        'onCommand',
        'onCompassNeedsCalibration',
        'onContextMenu',
        'onControlSelect',
        'onCopy',
        'onCueChange',
        'onCut',
        'onDataAvailable',
        'onDataSetChanged',
        'onDataSetComplete',
        'onDblClick',
        'onDeactivate',
        'onDeviceLight',
        'onDeviceMotion',
        'onDeviceOrientation',
        'onDeviceProximity',
        'onDrag',
        'onDragDrop',
        'onDragEnd',
        'onDragEnter',
        'onDragLeave',
        'onDragOver',
        'onDragStart',
        'onDrop',
        'onDurationChange',
        'onEmptied',
        'onEnd',
        'onEnded',
        'onError',
        'onErrorUpdate',
        'onExit',
        'onFilterChange',
        'onFinish',
        'onFocus',
        'onFocusIn',
        'onFocusOut',
        'onFormChange',
        'onFormInput',
        'onFullScreenChange',
        'onFullScreenError',
        'onGotPointerCapture',
        'onHashChange',
        'onHelp',
        'onInput',
        'onInvalid',
        'onKeyDown',
        'onKeyPress',
        'onKeyUp',
        'onLanguageChange',
        'onLayoutComplete',
        'onLoad',
        'onLoadedData',
        'onLoadedMetaData',
        'onLoadStart',
        'onLoseCapture',
        'onLostPointerCapture',
        'onMediaComplete',
        'onMediaError',
        'onMessage',
        'onMouseDown',
        'onMouseEnter',
        'onMouseLeave',
        'onMouseMove',
        'onMouseOut',
        'onMouseOver',
        'onMouseUp',
        'onMouseWheel',
        'onMove',
        'onMoveEnd',
        'onMoveStart',
        'onMozFullScreenChange',
        'onMozFullScreenError',
        'onMozPointerLockChange',
        'onMozPointerLockError',
        'onMsContentZoom',
        'onMsFullScreenChange',
        'onMsFullScreenError',
        'onMsGestureChange',
        'onMsGestureDoubleTap',
        'onMsGestureEnd',
        'onMsGestureHold',
        'onMsGestureStart',
        'onMsGestureTap',
        'onMsGotPointerCapture',
        'onMsInertiaStart',
        'onMsLostPointerCapture',
        'onMsManipulationStateChanged',
        'onMsPointerCancel',
        'onMsPointerDown',
        'onMsPointerEnter',
        'onMsPointerLeave',
        'onMsPointerMove',
        'onMsPointerOut',
        'onMsPointerOver',
        'onMsPointerUp',
        'onMsSiteModeJumpListItemRemoved',
        'onMsThumbnailClick',
        'onOffline',
        'onOnline',
        'onOutOfSync',
        'onPage',
        'onPageHide',
        'onPageShow',
        'onPaste',
        'onPause',
        'onPlay',
        'onPlaying',
        'onPointerCancel',
        'onPointerDown',
        'onPointerEnter',
        'onPointerLeave',
        'onPointerLockChange',
        'onPointerLockError',
        'onPointerMove',
        'onPointerOut',
        'onPointerOver',
        'onPointerUp',
        'onPopState',
        'onProgress',
        'onPropertyChange',
        'onRateChange',
        'onReadyStateChange',
        'onReceived',
        'onRepeat',
        'onReset',
        'onResize',
        'onResizeEnd',
        'onResizeStart',
        'onResume',
        'onReverse',
        'onRowDelete',
        'onRowEnter',
        'onRowExit',
        'onRowInserted',
        'onRowsDelete',
        'onRowsEnter',
        'onRowsExit',
        'onRowsInserted',
        'onScroll',
        'onSearch',
        'onSeek',
        'onSeeked',
        'onSeeking',
        'onSelect',
        'onSelectionChange',
        'onSelectStart',
        'onStalled',
        'onStorage',
        'onStorageCommit',
        'onStart',
        'onStop',
        'onShow',
        'onSyncRestored',
        'onSubmit',
        'onSuspend',
        'onSynchRestored',
        'onTimeError',
        'onTimeUpdate',
        'onTrackChange',
        'onTransitionEnd',
        'onToggle',
        'onTouchCancel',
        'onTouchStart',
        'onTransitionCancel',
        'onTransitionEnd',
        'onUnload',
        'onURLFlip',
        'onUserProximity',
        'onVolumeChange',
        'onWaiting',
        'onWebKitAnimationEnd',
        'onWebKitAnimationIteration',
        'onWebKitAnimationStart',
        'onWebKitFullScreenChange',
        'onWebKitFullScreenError',
        'onWebKitTransitionEnd',
        'onWheel',
    ];

    /**
     * @var array
     */
    private static $_never_allowed_str_afterwards = [
        'FSCOMMAND',
        '&lt;script&gt;',
        '&lt;/script&gt;',
    ];

    /**
     * https://www.owasp.org/index.php/XSS_Filter_Evasion_Cheat_Sheet#Event_Handlers
     *
     * @var array
     */
    private $_evil_attributes_regex = [
        'on\w*',
        'style',
        'xmlns:xdp',
        'formaction',
        'form',
        'xlink:href',
        'seekSegmentTime',
        'FSCommand',
    ];

    /**
     * @var array
     */
    private $_evil_html_tags = [
        'applet',
        'audio',
        'basefont',
        'base',
        'behavior',
        'bgsound',
        'blink',
        'body',
        'embed',
        'eval',
        'expression',
        'form',
        'frameset',
        'frame',
        'head',
        'html',
        'ilayer',
        'iframe',
        'input',
        'button',
        'select',
        'isindex',
        'layer',
        'link',
        'meta',
        'keygen',
        'object',
        'plaintext',
        'style',
        'script',
        'textarea',
        'title',
        'math',
        'video',
        'source',
        'svg',
        'xml',
    ];

    const VOKU_ANTI_XSS_STYLE = 'voku::anti-xss::STYLE';

    /**
     * @var string
     */
    private $_spacing_regex = '(?:\s|"|\042|\'|\047|\+|&#x09;|&#x0[A-F];|%0a)*+';

    /**
     * The replacement-string for not allowed strings.
     *
     * @var string
     */
    private $_replacement = '';

    /**
     * List of never allowed strings.
     *
     * @var array
     */
    private $_never_allowed_str = [];

    /**
     * If your DB (MySQL) encoding is "utf8" and not "utf8mb4", then
     * you can't save 4-Bytes chars from UTF-8 and someone can create stored XSS-attacks.
     *
     * @var bool
     */
    private $_stripe_4byte_chars = false;

    /**
     * @var bool|null
     */
    private $_xss_found;

    /**
     * __construct()
     */
    public function __construct()
    {
        $this->_initNeverAllowedStr();
    }

    /**
     * Compact any exploded words.
     *
     * <p>
     * <br />
     * INFO: This corrects words like:  j a v a s c r i p t
     * <br />
     * These words are compacted back to their correct state.
     * </p>
     *
     * @param string $str
     *
     * @return string
     */
    private function _compact_exploded_javascript(string $str): string
    {
        static $WORDS_CACHE;
        $WORDS_CACHE['chunk'] = [];
        $WORDS_CACHE['split'] = [];

        $words = [
            'javascript',
            '<script',
            '</script>',
            'base64',
            'document',
            'eval',
        ];

        // check if we need to perform the regex-stuff
        if (\strlen($str) <= 30) {
            $useStrPos = true;
        } else {
            $useStrPos = false;
        }

        foreach ($words as $word) {
            if (!isset($WORDS_CACHE['chunk'][$word])) {
                $regex = $this->_spacing_regex;
                $WORDS_CACHE['chunk'][$word] = \substr(
                    \chunk_split($word, 1, $regex),
                    0,
                    -\strlen($regex)
                );

                $WORDS_CACHE['split'][$word] = \str_split($word, 1);
            }

            if ($useStrPos) {
                foreach ($WORDS_CACHE['split'][$word] as $charTmp) {
                    if (\stripos($str, $charTmp) === false) {
                        continue 2;
                    }
                }
            }

            // We only want to do this when it is followed by a non-word character.
            // And if there are no char at the start of the string.
            //
            // That way valid stuff like "dealer to!" does not become "dealerto".

            $regex = '#(?<before>[^\p{L}]|^)(?<word>' . \str_replace(['#', '.'], ['\#', '\.'], $WORDS_CACHE['chunk'][$word]) . ')(?<after>[^\p{L}|@|.|!|?| ]|$)#ius';
            $str = (string) \preg_replace_callback(
                $regex,
                function ($matches) {
                    return $this->_compact_exploded_words_callback($matches);
                },
                $str
            );
        }

        return $str;
    }

    /**
     * Compact exploded words.
     *
     * <p>
     * <br />
     * INFO: Callback method for xss_clean() to remove whitespace from things like 'j a v a s c r i p t'.
     * </p>
     *
     * @param array $matches
     *
     * @return  string
     */
    private function _compact_exploded_words_callback($matches): string
    {
        return $matches['before'] . \preg_replace(
            '/' . $this->_spacing_regex . '/is',
            '',
            $matches['word']
            ) . $matches['after'];
    }

    /**
     * HTML-Entity decode callback.
     *
     * @param array $match
     *
     * @return string
     */
    private function _decode_entity(array $match): string
    {
        // init
        $str = $match[0];

        // protect GET variables without XSS in URLs
        if (\preg_match_all("/[\?|&]?[\\p{L}0-9_\-\[\]]+\s*=\s*(?<wrapped>\"|\042|'|\047)(?<attr>[^\\1]*?)\\g{wrapped}/ui", $str, $matches)) {
            if (isset($matches['attr'])) {
                foreach ($matches['attr'] as $matchInner) {
                    $tmpAntiXss = clone $this;

                    $urlPartClean = $tmpAntiXss->xss_clean($matchInner);

                    if ($tmpAntiXss->isXssFound() === true) {
                        $this->_xss_found = true;

                        $str = \str_replace($matchInner, UTF8::rawurldecode($urlPartClean), $str);
                    }
                }
            }
        } else {
            $str = $this->_entity_decode(UTF8::rawurldecode($str));
        }

        return $str;
    }

    /**
     * Decode the html-tags via "UTF8::html_entity_decode()" or the string via "UTF8::rawurldecode()".
     *
     * @param string $str
     *
     * @return string
     */
    private function _decode_string(string $str): string
    {
        // init
        $regExForHtmlTags = '/<\p{L}+.*+/us';

        if (
            \strpos($str, '<') !== false
            &&
            \preg_match($regExForHtmlTags, $str, $matches) === 1
        ) {
            $str = (string) \preg_replace_callback(
                $regExForHtmlTags,
                function ($matches) {
                    return $this->_decode_entity($matches);
                },
                $str
            );
        } else {
            $str = UTF8::rawurldecode($str);
        }

        return $str;
    }

    /**
     * @param string $str
     *
     * @return mixed
     */
    private function _do($str)
    {
        $str = (string) $str;
        $strInt = (int) $str;
        $strFloat = (float) $str;
        if (
            !$str
            ||
            (string) $strInt === $str
            ||
            (string) $strFloat === $str
        ) {

            // no xss found
            if ($this->_xss_found !== true) {
                $this->_xss_found = false;
            }

            return $str;
        }

        // remove the BOM from UTF-8 / UTF-16 / UTF-32 strings
        $str = UTF8::remove_bom($str);

        // replace the diamond question mark (�) and invalid-UTF8 chars
        $str = UTF8::replace_diamond_question_mark($str, '');

        // replace invisible characters with one single space
        $str = UTF8::remove_invisible_characters($str, true, ' ');

        // normalize the whitespace
        $str = UTF8::normalize_whitespace($str);

        // decode UTF-7 characters
        $str = $this->_repack_utf7($str);

        // decode the string
        $str = $this->_decode_string($str);

        // remove all >= 4-Byte chars if needed
        if ($this->_stripe_4byte_chars) {
            $str = (string) \preg_replace('/[\x{10000}-\x{10FFFF}]/u', '', $str);
        }

        // backup the string (for later comparision)
        $str_backup = $str;

        // remove strings that are never allowed
        $str = $this->_do_never_allowed($str);

        // corrects words before the browser will do it
        $str = $this->_compact_exploded_javascript($str);

        // remove disallowed javascript calls in links, images etc.
        $str = $this->_remove_disallowed_javascript($str);

        // remove evil attributes such as style, onclick and xmlns
        $str = $this->_remove_evil_attributes($str);

        // sanitize naughty JavaScript elements
        $str = $this->_sanitize_naughty_javascript($str);

        // sanitize naughty HTML elements
        $str = $this->_sanitize_naughty_html($str);

        // final clean up
        //
        // -> This adds a bit of extra precaution in case something got through the above filters.
        $str = $this->_do_never_allowed_afterwards($str);

        // check for xss
        if ($this->_xss_found !== true) {
            $this->_xss_found = !($str_backup === $str);
        }

        return $str;
    }

    /**
     * Remove never allowed strings.
     *
     * @param string $str
     *
     * @return string
     */
    private function _do_never_allowed(string $str): string
    {
        static $NEVER_ALLOWED_CACHE = [];

        $NEVER_ALLOWED_CACHE['keys'] = null;
        $NEVER_ALLOWED_CACHE['regex'] = null;

        if ($NEVER_ALLOWED_CACHE['keys'] === null) {
            $NEVER_ALLOWED_CACHE['keys'] = \array_keys($this->_never_allowed_str);
        }

        $str = \str_ireplace(
            $NEVER_ALLOWED_CACHE['keys'],
            $this->_never_allowed_str,
            $str
        );

        // ---

        foreach (self::$_never_allowed_call as $call) {
            if (\stripos($str, $call) !== false) {
                $str = (string) \preg_replace(
                    '#([^\p{L}]|^)' . $call . '\s*:#ius',
                    '$1' . $this->_replacement,
                    $str
                );
            }
        }

        // ---

        if ($NEVER_ALLOWED_CACHE['regex'] === null) {
            $NEVER_ALLOWED_CACHE['regex'] = \implode('|', self::$_never_allowed_regex);
        }

        $str = (string) \preg_replace(
            '#' . $NEVER_ALLOWED_CACHE['regex'] . '#ius',
            $this->_replacement,
            $str
        );

        return $str;
    }

    /**
     * Remove never allowed string, afterwards.
     *
     * <p>
     * <br />
     * INFO: clean-up also some string, if there is no html-tag
     * </p>
     *
     * @param string $str
     *
     * @return  string
     */
    private function _do_never_allowed_afterwards(string $str): string
    {
        if (\stripos($str, 'on') !== false) {
            foreach (self::$_never_allowed_on_events_afterwards as $event) {
                if (\stripos($str, $event) !== false) {
                    $regex = '(?<before>[^\p{L}]|^)(?:' . $event . ')(?<after>\s|[^\p{L}]|$)';

                    do {
                        $count = $temp_count = 0;

                        $str = (string) \preg_replace(
                            '#' . $regex . '#ius',
                            '$1' . $this->_replacement . '$2',
                            $str,
                            -1,
                            $temp_count
                        );
                        $count += $temp_count;
                    } while ($count);
                }
            }
        }

        return (string) \str_ireplace(
            self::$_never_allowed_str_afterwards,
            $this->_replacement,
            $str
        );
    }

    /**
     * Entity-decoding.
     *
     * @param string $str
     *
     * @return string
     */
    private function _entity_decode(string $str): string
    {
        static $HTML_ENTITIES_CACHE;

        $flags = ENT_QUOTES | ENT_HTML5 | ENT_DISALLOWED | ENT_SUBSTITUTE;

        // decode
        $str = UTF8::html_entity_decode($str, $flags);

        // decode-again, for e.g. HHVM or miss configured applications ...
        if (\preg_match_all('/(?<html_entity>&[A-Za-z]{2,}[;]{0})/', $str, $matches)) {
            if ($HTML_ENTITIES_CACHE === null) {

                // links:
                // - http://dev.w3.org/html5/html-author/charref
                // - http://www.w3schools.com/charsets/ref_html_entities_n.asp
                $entitiesSecurity = [
                    '&#x00000;'          => '',
                    '&#0;'               => '',
                    '&#x00001;'          => '',
                    '&#1;'               => '',
                    '&nvgt;'             => '',
                    '&#61253;'           => '',
                    '&#x0EF45;'          => '',
                    '&shy;'              => '',
                    '&#x000AD;'          => '',
                    '&#173;'             => '',
                    '&colon;'            => ':',
                    '&#x0003A;'          => ':',
                    '&#58;'              => ':',
                    '&lpar;'             => '(',
                    '&#x00028;'          => '(',
                    '&#40;'              => '(',
                    '&rpar;'             => ')',
                    '&#x00029;'          => ')',
                    '&#41;'              => ')',
                    '&quest;'            => '?',
                    '&#x0003F;'          => '?',
                    '&#63;'              => '?',
                    '&sol;'              => '/',
                    '&#x0002F;'          => '/',
                    '&#47;'              => '/',
                    '&apos;'             => '\'',
                    '&#x00027;'          => '\'',
                    '&#039;'             => '\'',
                    '&#39;'              => '\'',
                    '&#x27;'             => '\'',
                    '&bsol;'             => '\'',
                    '&#x0005C;'          => '\\',
                    '&#92;'              => '\\',
                    '&comma;'            => ',',
                    '&#x0002C;'          => ',',
                    '&#44;'              => ',',
                    '&period;'           => '.',
                    '&#x0002E;'          => '.',
                    '&quot;'             => '"',
                    '&QUOT;'             => '"',
                    '&#x00022;'          => '"',
                    '&#34;'              => '"',
                    '&grave;'            => '`',
                    '&DiacriticalGrave;' => '`',
                    '&#x00060;'          => '`',
                    '&#96;'              => '`',
                    '&#46;'              => '.',
                    '&equals;'           => '=',
                    '&#x0003D;'          => '=',
                    '&#61;'              => '=',
                    '&newline;'          => "\n",
                    '&#x0000A;'          => "\n",
                    '&#10;'              => "\n",
                    '&tab;'              => "\t",
                    '&#x00009;'          => "\t",
                    '&#9;'               => "\t",
                ];

                $HTML_ENTITIES_CACHE = \array_merge(
                    $entitiesSecurity,
                    \array_flip(\get_html_translation_table(HTML_ENTITIES, $flags)),
                    \array_flip(self::_get_data('entities_fallback'))
                );
            }

            $search = [];
            $replace = [];
            foreach ($matches['html_entity'] as $match) {
                $match .= ';';
                if (isset($HTML_ENTITIES_CACHE[$match])) {
                    $search[$match] = $match;
                    $replace[$match] = $HTML_ENTITIES_CACHE[$match];
                }
            }

            if (\count($replace) > 0) {
                $str = \str_replace($search, $replace, $str);
            }
        }

        return $str;
    }

    /**
     * get data from "/data/*.php"
     *
     * @param string $file
     *
     * @return mixed
     */
    private static function _get_data(string $file)
    {
        /** @noinspection PhpIncludeInspection */
        return include __DIR__ . '/data/' . $file . '.php';
    }

    /**
     * Filters tag attributes for consistency and safety.
     *
     * @param string $str
     *
     * @return string
     */
    private function _filter_attributes(string $str): string
    {
        if ($str === '') {
            return '';
        }

        $out = '';
        if (\preg_match_all('#\s*[\p{L}0-9_\-\[\]]+\s*=\s*("|\042|\'|\047)(?:[^\\1]*?)\\1#ui', $str, $matches)) {
            foreach ($matches[0] as $match) {
                $out .= $match;
            }
        }

        return $out;
    }

    /**
     * initialize "$this->_never_allowed_str"
     */
    private function _initNeverAllowedStr()
    {
        $this->_never_allowed_str = [
            'document.cookie'   => $this->_replacement,
            '(document).cookie' => $this->_replacement,
            'document.write'    => $this->_replacement,
            '(document).write'  => $this->_replacement,
            '.parentNode'       => $this->_replacement,
            '.innerHTML'        => $this->_replacement,
            '.appendChild'      => $this->_replacement,
            '-moz-binding'      => $this->_replacement,
            '<!--'              => '&lt;!--',
            '-->'               => '--&gt;',
            '<?'                => '&lt;?',
            '?>'                => '?&gt;',
            '<![CDATA['         => '&lt;![CDATA[',
            '<!ENTITY'          => '&lt;!ENTITY',
            '<!DOCTYPE'         => '&lt;!DOCTYPE',
            '<!ATTLIST'         => '&lt;!ATTLIST',
        ];
    }

    /**
     * Callback method for xss_clean() to sanitize links.
     *
     * <p>
     * <br />
     * INFO: This limits the PCRE backtracks, making it more performance friendly
     * and prevents PREG_BACKTRACK_LIMIT_ERROR from being triggered in
     * PHP 5.2+ on link-heavy strings.
     * </p>
     *
     * @param array $match
     *
     * @return string
     */
    private function _js_link_removal_callback(array $match): string
    {
        return $this->_js_removal_callback($match, 'href');
    }

    /**
     * Callback method for xss_clean() to sanitize tags.
     *
     * <p>
     * <br />
     * INFO: This limits the PCRE backtracks, making it more performance friendly
     * and prevents PREG_BACKTRACK_LIMIT_ERROR from being triggered in
     * PHP 5.2+ on image tag heavy strings.
     * </p>
     *
     * @param array  $match
     * @param string $search
     *
     * @return string
     */
    private function _js_removal_callback(array $match, string $search): string
    {
        if (!$match[0]) {
            return '';
        }

        // init
        $match_style_matched = false;
        $match_style = [];

        // hack for style attributes v1
        if (
            $search === 'href'
            &&
            \stripos($match[0], 'style') !== false
        ) {
            \preg_match('/style=".*?"/i', $match[0], $match_style);
            $match_style_matched = (\count($match_style) > 0);
            if ($match_style_matched) {
                $match[0] = \str_replace($match_style[0], self::VOKU_ANTI_XSS_STYLE, $match[0]);
            }
        }

        $replacer = $this->_filter_attributes(\str_replace(['<', '>'], '', $match[1]));

        // filter for "(.*)" but only in the "$search"-attribute
        if (\stripos($replacer, $search) !== false) {
            $pattern = '#' . $search . '=(?<wrapper>(?:\'|\047)|(?:"|\042)).*(?:\g{wrapper})#isU';
            $matchInner = [];
            $foundSomethingBad = false;
            \preg_match($pattern, $match[1], $matchInner);
            if (
                \count($matchInner) > 0
                &&
                \preg_match('#(?:\(.*([^\)]*?)(?:\)))#s', $matchInner[0])
            ) {
                $foundSomethingBad = true;

                $replacer = (string) \preg_replace(
                    $pattern,
                    $search . '="' . $this->_replacement . '"',
                    $replacer
                );
            }

            if (!$foundSomethingBad) {
                // filter for javascript
                $pattern = '#' . $search . '=.*(?:javascript:|view-source:|livescript:|wscript:|vbscript:|mocha:|charset=|window\.|\(?document\)?\.|\.cookie|<script|d\s*a\s*t\s*a\s*:)#ius';
                $matchInner = [];
                \preg_match($pattern, $match[1], $matchInner);
                if (\count($matchInner) > 0) {
                    $replacer = (string) \preg_replace(
                        $pattern,
                        $search . '="' . $this->_replacement . '"',
                        $replacer
                    );
                }
            }
        }

        $return = \str_ireplace($match[1], $replacer, (string) $match[0]);

        // hack for style attributes v2
        if (
            $match_style_matched
            &&
            $search === 'href'
        ) {
            $return = \str_replace(self::VOKU_ANTI_XSS_STYLE, $match_style[0], $return);
        }

        return $return;
    }

    /**
     * Callback method for xss_clean() to sanitize image tags.
     *
     * <p>
     * <br />
     * INFO: This limits the PCRE backtracks, making it more performance friendly
     * and prevents PREG_BACKTRACK_LIMIT_ERROR from being triggered in
     * PHP 5.2+ on image tag heavy strings.
     * </p>
     *
     * @param array $match
     *
     * @return string
     */
    private function _js_src_removal_callback(array $match): string
    {
        return $this->_js_removal_callback($match, 'src');
    }

    /**
     * Remove disallowed Javascript in links or img tags
     *
     * <p>
     * <br />
     * We used to do some version comparisons and use of stripos(),
     * but it is dog slow compared to these simplified non-capturing
     * preg_match(), especially if the pattern exists in the string
     * </p>
     *
     * <p>
     * <br />
     * Note: It was reported that not only space characters, but all in
     * the following pattern can be parsed as separators between a tag name
     * and its attributes: [\d\s"\'`;,\/\=\(\x00\x0B\x09\x0C]
     * ... however, UTF8::clean() above already strips the
     * hex-encoded ones, so we'll skip them below.
     * </p>
     *
     * @param string $str
     *
     * @return string
     */
    private function _remove_disallowed_javascript($str): string
    {
        do {
            $original = $str;

            if (\stripos($str, '<a') !== false) {
                $str = (string) \preg_replace_callback(
                    '#<a[^\p{L}@>]+([^>]*?)(?:>|$)#iu',
                    function ($matches) {
                        return $this->_js_link_removal_callback($matches);
                    },
                    $str
                );
            }

            if (\stripos($str, '<img') !== false) {
                $str = (string) \preg_replace_callback(
                    '#<img[^\p{L}@]+([^>]*?)(?:\s?/?>|$)#iu',
                    function ($matches) {
                        return $this->_js_src_removal_callback($matches);
                    },
                    $str
                );
            }

            if (\stripos($str, '<audio') !== false) {
                $str = (string) \preg_replace_callback(
                    '#<audio[^\p{L}@]+([^>]*?)(?:\s?/?>|$)#iu',
                    function ($matches) {
                        return $this->_js_src_removal_callback($matches);
                    },
                    $str
                );
            }

            if (\stripos($str, '<video') !== false) {
                $str = (string) \preg_replace_callback(
                    '#<video[^\p{L}@]+([^>]*?)(?:\s?/?>|$)#iu',
                    function ($matches) {
                        return $this->_js_src_removal_callback($matches);
                    },
                    $str
                );
            }

            if (\stripos($str, '<source') !== false) {
                $str = (string) \preg_replace_callback(
                    '#<source[^\p{L}@]+([^>]*?)(?:\s?/?>|$)#iu',
                    function ($matches) {
                        return $this->_js_src_removal_callback($matches);
                    },
                    $str
                );
            }

            if (\stripos($str, 'script') !== false) {
                // US-ASCII: ¼ === <
                $str = (string) \preg_replace(
                    '#(?:¼|<)/*(?:script).*(?:¾|>)#isuU',
                    $this->_replacement,
                    $str
                );
            }
        } while ($original !== $str);

        return (string) $str;
    }

    /**
     * Remove Evil HTML Attributes (like event handlers and style).
     *
     * It removes the evil attribute and either:
     *
     *  - Everything up until a space. For example, everything between the pipes:
     *
     * <code>
     *   <a |style=document.write('hello');alert('world');| class=link>
     * </code>
     *
     *  - Everything inside the quotes. For example, everything between the pipes:
     *
     * <code>
     *   <a |style="document.write('hello'); alert('world');"| class="link">
     * </code>
     *
     * @param string $str <p>The string to check.</p>
     *
     * @return string the string with the evil attributes removed
     */
    private function _remove_evil_attributes($str): string
    {
        // replace style-attribute, first (if needed)
        if (
            \stripos($str, 'style') !== false
            &&
            \in_array('style', $this->_evil_attributes_regex, true)
        ) {
            do {
                $count = $temp_count = 0;

                $str = (string) \preg_replace(
                    '/(<[^>]+)(?<!\p{L})(style\s*=\s*"(?:[^"]*?)"|style\s*=\s*\'(?:[^\']*?)\')/iu',
                    '$1' . $this->_replacement,
                    $str,
                    -1,
                    $temp_count
                );
                $count += $temp_count;
            } while ($count);
        }

        $evil_attributes_string = \implode('|', $this->_evil_attributes_regex);
        do {
            $count = $temp_count = 0;

            // find occurrences of illegal attribute strings with and without quotes (042 ["] and 047 ['] are octal quotes)
            $str = (string) \preg_replace(
                '/(.*)((?:<[^>]+)(?<!\p{L}))(?:' . $evil_attributes_string . ')(?:\s*=\s*)(?:(?:\'|\047)(?:.*?)(?:\'|\047)|(?:"|\042)(?:.*?)(?:"|\042))(.*)/ius',
                '$1$2' . $this->_replacement . '$3$4',
                $str,
                -1,
                $temp_count
            );
            $count += $temp_count;

            $str = (string) \preg_replace(
                '/(.*)(<[^>]+)(?<!\p{L})(?:' . $evil_attributes_string . ')\s*=\s*(?:[^\s>]*)(.*)/ius',
                '$1$2' . $this->_replacement . '$3',
                $str,
                -1,
                $temp_count
            );
            $count += $temp_count;
        } while ($count);

        return (string) $str;
    }

    /**
     * UTF-7 decoding function.
     *
     * @param string $str <p>HTML document for recode ASCII part of UTF-7 back to ASCII.</p>
     *
     * @return string
     */
    private function _repack_utf7(string $str): string
    {
        if (\strpos($str, '-') === false) {
            return $str;
        }

        return (string) \preg_replace_callback(
            '#\+([\p{L}0-9]+)\-#ui',
            function ($matches) {
                return $this->_repack_utf7_callback($matches);
            },
            $str
        );
    }

    /**
     * Additional UTF-7 decoding function.
     *
     * @param string[] $strings <p>Array of strings for recode ASCII part of UTF-7 back to ASCII.</p>
     *
     * @return string
     */
    private function _repack_utf7_callback(array $strings): string
    {
        $strTmp = \base64_decode($strings[1], true);

        if ($strTmp === false) {
            return $strings[0];
        }

        if (\rtrim(\base64_encode($strTmp), '=') !== \rtrim($strings[1], '=')) {
            return $strings[0];
        }

        $string = (string) \preg_replace_callback(
            '/^((?:\x00.)*?)((?:[^\x00].)+)/us',
            function ($matches) {
                return $this->_repack_utf7_callback_back($matches);
            },
            $strTmp
        );

        return (string) \preg_replace(
            '/\x00(.)/us',
            '$1',
            $string
        );
    }

    /**
     * Additional UTF-7 encoding function.
     *
     * @param string $str <p>String for recode ASCII part of UTF-7 back to ASCII.</p>
     *
     * @return string
     */
    private function _repack_utf7_callback_back($str): string
    {
        return $str[1] . '+' . \rtrim(\base64_encode($str[2]), '=') . '-';
    }

    /**
     * Sanitize naughty HTML elements.
     *
     * <p>
     * <br />
     *
     * If a tag containing any of the words in the list
     * below is found, the tag gets converted to entities.
     *
     * <br /><br />
     *
     * So this: <blink>
     * <br />
     * Becomes: &lt;blink&gt;
     * </p>
     *
     * @param string $str
     *
     * @return string
     */
    private function _sanitize_naughty_html($str): string
    {
        if (\strpos($str, '<') === false) {
            return $str;
        }

        $evil_html_tags = \implode('|', $this->_evil_html_tags);
        $str = (string) \preg_replace_callback(
            '#<(?<start>/*\s*)(?<content>' . $evil_html_tags . ')(?<end>[^><]*)(?<rest>[><]*)#ius',
            function ($matches) {
                return $this->_sanitize_naughty_html_callback($matches);
            },
            $str
        );

        return (string) $str;
    }

    /**
     * Sanitize naughty HTML.
     *
     * <p>
     * <br />
     * Callback method for AntiXSS->sanitize_naughty_html() to remove naughty HTML elements.
     * </p>
     *
     * @param array $matches
     *
     * @return string
     */
    private function _sanitize_naughty_html_callback(array $matches): string
    {
        $fullMatch = $matches[0];

        // skip some edge-cases
        if (
            $fullMatch !== '<' . $matches['content']
            &&
            \strpos($fullMatch, '=') === false
            &&
            \strpos($fullMatch, ' ') === false
            &&
            \strpos($fullMatch, ':') === false
            &&
            \strpos($fullMatch, '/') === false
            &&
            \strpos($fullMatch, '\\') === false
            &&
            \strpos($fullMatch, '<' . $matches['content'] . '>') !== 0
            &&
            \strpos($fullMatch, '</' . $matches['content'] . '>') !== 0
            &&
            \strpos($fullMatch, '<' . $matches['content'] . '<') !== 0
        ) {
            return $fullMatch;
        }

        return '&lt;' . $matches['start'] . $matches['content'] . $matches['end'] // encode opening brace
            // encode captured opening or closing brace to prevent recursive vectors:
            . \str_replace(
                [
                    '>',
                    '<',
                ],
                [
                    '&gt;',
                    '&lt;',
                ],
                $matches['rest']
            );
    }

    /**
     * Sanitize naughty scripting elements
     *
     * <p>
     * <br />
     *
     * Similar to above, only instead of looking for
     * tags it looks for PHP and JavaScript commands
     * that are disallowed. Rather than removing the
     * code, it simply converts the parenthesis to entities
     * rendering the code un-executable.
     *
     * <br /><br />
     *
     * For example:  <pre>eval('some code')</pre>
     * <br />
     * Becomes:      <pre>eval&#40;'some code'&#41;</pre>
     * </p>
     *
     * @param string $str
     *
     * @return string
     */
    private function _sanitize_naughty_javascript($str): string
    {
        $str = (string) \preg_replace(
            '#(alert|eval|prompt|confirm|cmd|passthru|eval|exec|expression|system|fopen|fsockopen|file|file_get_contents|readfile|unlink)(\s*)\((.*)\)#uisU',
            '\\1\\2&#40;\\3&#41;',
            $str
        );

        return (string) $str;
    }

    /**
     * Add some strings to the "_evil_attributes"-array.
     *
     * @param string[] $strings
     *
     * @return $this
     */
    public function addEvilAttributes(array $strings): self
    {
        $this->_evil_attributes_regex = \array_merge(
            $strings,
            $this->_evil_attributes_regex
        );

        return $this;
    }

    /**
     * Add some strings to the "_evil_html_tags"-array.
     *
     * @param string[] $strings
     *
     * @return $this
     */
    public function addEvilHtmlTags(array $strings): self
    {
        $this->_evil_html_tags = \array_merge($strings, $this->_evil_html_tags);

        return $this;
    }

    /**
     * Check if the "AntiXSS->xss_clean()"-method found an XSS attack in the last run.
     *
     * @return bool|null will return null if the "xss_clean()" wan't running at all
     */
    public function isXssFound()
    {
        return $this->_xss_found;
    }

    /**
     * Remove some strings from the "_evil_attributes"-array.
     *
     * <p>
     * <br />
     * WARNING: Use this method only if you have a really good reason.
     * </p>
     *
     * @param string[] $strings
     *
     * @return $this
     */
    public function removeEvilAttributes(array $strings): self
    {
        $this->_evil_attributes_regex = \array_diff(
            $this->_evil_attributes_regex,
            \array_intersect($strings, $this->_evil_attributes_regex)
        );

        return $this;
    }

    /**
     * Remove some strings from the "_evil_html_tags"-array.
     *
     * <p>
     * <br />
     * WARNING: Use this method only if you have a really good reason.
     * </p>
     *
     * @param string[] $strings
     *
     * @return $this
     */
    public function removeEvilHtmlTags(array $strings): self
    {
        $this->_evil_html_tags = \array_diff(
            $this->_evil_html_tags,
            \array_intersect($strings, $this->_evil_html_tags)
        );

        return $this;
    }

    /**
     * Set the replacement-string for not allowed strings.
     *
     * @param string $string
     *
     * @return $this
     */
    public function setReplacement($string): self
    {
        $this->_replacement = (string) $string;

        $this->_initNeverAllowedStr();

        return $this;
    }

    /**
     * Set the option to stripe 4-Byte chars.
     *
     * <p>
     * <br />
     * INFO: use it if your DB (MySQL) can't use "utf8mb4" -> preventing stored XSS-attacks
     * </p>
     *
     * @param bool $bool
     *
     * @return $this
     */
    public function setStripe4byteChars($bool): self
    {
        $this->_stripe_4byte_chars = (bool) $bool;

        return $this;
    }

    /**
     * XSS Clean
     *
     * <p>
     * <br />
     * Sanitizes data so that "Cross Site Scripting" hacks can be
     * prevented. This method does a fair amount of work but
     * it is extremely thorough, designed to prevent even the
     * most obscure XSS attempts. But keep in mind that nothing
     * is ever 100% foolproof...
     * </p>
     *
     * <p>
     * <br />
     * <strong>Note:</strong> Should only be used to deal with data upon submission.
     *   It's not something that should be used for general
     *   runtime processing.
     * </p>
     *
     * @see http://channel.bitflux.ch/wiki/XSS_Prevention
     *    Based in part on some code and ideas from Bitflux.
     * @see http://ha.ckers.org/xss.html
     *    To help develop this script I used this great list of
     *    vulnerabilities along with a few other hacks I've
     *    harvested from examining vulnerabilities in other programs.
     *
     * @param array|mixed $str <p>input data e.g. string or array of strings</p>
     *
     * @return mixed
     */
    public function xss_clean($str)
    {
        // reset
        $this->_xss_found = null;

        // check for an array of strings
        if (\is_array($str)) {
            foreach ($str as $key => &$value) {
                $str[$key] = $this->xss_clean($value);
            }

            return $str;
        }

        $old_str_backup = $str;

        // process
        do {
            $old_str = $str;
            $str = $this->_do($str);
        } while ($old_str !== $str);

        // keep the old value, if there wasn't any XSS attack
        if ($this->_xss_found !== true) {
            $str = $old_str_backup;
        }

        return $str;
    }
}
