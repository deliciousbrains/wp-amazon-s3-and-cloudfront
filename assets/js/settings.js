(function (global, factory) {
    typeof exports === 'object' && typeof module !== 'undefined' ? module.exports = factory() :
    typeof define === 'function' && define.amd ? define(factory) :
    (global = typeof globalThis !== 'undefined' ? globalThis : global || self, global.AS3CF_Settings = factory());
})(this, (function () { 'use strict';

    function noop() { }
    const identity = x => x;
    function assign(tar, src) {
        // @ts-ignore
        for (const k in src)
            tar[k] = src[k];
        return tar;
    }
    function is_promise(value) {
        return value && typeof value === 'object' && typeof value.then === 'function';
    }
    function add_location(element, file, line, column, char) {
        element.__svelte_meta = {
            loc: { file, line, column, char }
        };
    }
    function run(fn) {
        return fn();
    }
    function blank_object() {
        return Object.create(null);
    }
    function run_all(fns) {
        fns.forEach(run);
    }
    function is_function(thing) {
        return typeof thing === 'function';
    }
    function safe_not_equal(a, b) {
        return a != a ? b == b : a !== b || ((a && typeof a === 'object') || typeof a === 'function');
    }
    let src_url_equal_anchor;
    function src_url_equal(element_src, url) {
        if (!src_url_equal_anchor) {
            src_url_equal_anchor = document.createElement('a');
        }
        src_url_equal_anchor.href = url;
        return element_src === src_url_equal_anchor.href;
    }
    function is_empty(obj) {
        return Object.keys(obj).length === 0;
    }
    function validate_store(store, name) {
        if (store != null && typeof store.subscribe !== 'function') {
            throw new Error(`'${name}' is not a store with a 'subscribe' method`);
        }
    }
    function subscribe(store, ...callbacks) {
        if (store == null) {
            return noop;
        }
        const unsub = store.subscribe(...callbacks);
        return unsub.unsubscribe ? () => unsub.unsubscribe() : unsub;
    }
    function get_store_value(store) {
        let value;
        subscribe(store, _ => value = _)();
        return value;
    }
    function component_subscribe(component, store, callback) {
        component.$$.on_destroy.push(subscribe(store, callback));
    }
    function create_slot(definition, ctx, $$scope, fn) {
        if (definition) {
            const slot_ctx = get_slot_context(definition, ctx, $$scope, fn);
            return definition[0](slot_ctx);
        }
    }
    function get_slot_context(definition, ctx, $$scope, fn) {
        return definition[1] && fn
            ? assign($$scope.ctx.slice(), definition[1](fn(ctx)))
            : $$scope.ctx;
    }
    function get_slot_changes(definition, $$scope, dirty, fn) {
        if (definition[2] && fn) {
            const lets = definition[2](fn(dirty));
            if ($$scope.dirty === undefined) {
                return lets;
            }
            if (typeof lets === 'object') {
                const merged = [];
                const len = Math.max($$scope.dirty.length, lets.length);
                for (let i = 0; i < len; i += 1) {
                    merged[i] = $$scope.dirty[i] | lets[i];
                }
                return merged;
            }
            return $$scope.dirty | lets;
        }
        return $$scope.dirty;
    }
    function update_slot_base(slot, slot_definition, ctx, $$scope, slot_changes, get_slot_context_fn) {
        if (slot_changes) {
            const slot_context = get_slot_context(slot_definition, ctx, $$scope, get_slot_context_fn);
            slot.p(slot_context, slot_changes);
        }
    }
    function get_all_dirty_from_scope($$scope) {
        if ($$scope.ctx.length > 32) {
            const dirty = [];
            const length = $$scope.ctx.length / 32;
            for (let i = 0; i < length; i++) {
                dirty[i] = -1;
            }
            return dirty;
        }
        return -1;
    }
    function exclude_internal_props(props) {
        const result = {};
        for (const k in props)
            if (k[0] !== '$')
                result[k] = props[k];
        return result;
    }
    function set_store_value(store, ret, value) {
        store.set(value);
        return ret;
    }
    function action_destroyer(action_result) {
        return action_result && is_function(action_result.destroy) ? action_result.destroy : noop;
    }

    const is_client = typeof window !== 'undefined';
    let now = is_client
        ? () => window.performance.now()
        : () => Date.now();
    let raf = is_client ? cb => requestAnimationFrame(cb) : noop;

    const tasks = new Set();
    function run_tasks(now) {
        tasks.forEach(task => {
            if (!task.c(now)) {
                tasks.delete(task);
                task.f();
            }
        });
        if (tasks.size !== 0)
            raf(run_tasks);
    }
    /**
     * Creates a new task that runs on each raf frame
     * until it returns a falsy value or is aborted
     */
    function loop(callback) {
        let task;
        if (tasks.size === 0)
            raf(run_tasks);
        return {
            promise: new Promise(fulfill => {
                tasks.add(task = { c: callback, f: fulfill });
            }),
            abort() {
                tasks.delete(task);
            }
        };
    }
    function append(target, node) {
        target.appendChild(node);
    }
    function get_root_for_style(node) {
        if (!node)
            return document;
        const root = node.getRootNode ? node.getRootNode() : node.ownerDocument;
        if (root && root.host) {
            return root;
        }
        return node.ownerDocument;
    }
    function append_empty_stylesheet(node) {
        const style_element = element('style');
        append_stylesheet(get_root_for_style(node), style_element);
        return style_element.sheet;
    }
    function append_stylesheet(node, style) {
        append(node.head || node, style);
    }
    function insert(target, node, anchor) {
        target.insertBefore(node, anchor || null);
    }
    function detach(node) {
        node.parentNode.removeChild(node);
    }
    function destroy_each(iterations, detaching) {
        for (let i = 0; i < iterations.length; i += 1) {
            if (iterations[i])
                iterations[i].d(detaching);
        }
    }
    function element(name) {
        return document.createElement(name);
    }
    function svg_element(name) {
        return document.createElementNS('http://www.w3.org/2000/svg', name);
    }
    function text(data) {
        return document.createTextNode(data);
    }
    function space() {
        return text(' ');
    }
    function empty() {
        return text('');
    }
    function listen(node, event, handler, options) {
        node.addEventListener(event, handler, options);
        return () => node.removeEventListener(event, handler, options);
    }
    function prevent_default(fn) {
        return function (event) {
            event.preventDefault();
            // @ts-ignore
            return fn.call(this, event);
        };
    }
    function attr(node, attribute, value) {
        if (value == null)
            node.removeAttribute(attribute);
        else if (node.getAttribute(attribute) !== value)
            node.setAttribute(attribute, value);
    }
    function children(element) {
        return Array.from(element.childNodes);
    }
    function set_input_value(input, value) {
        input.value = value == null ? '' : value;
    }
    function set_style(node, key, value, important) {
        if (value === null) {
            node.style.removeProperty(key);
        }
        else {
            node.style.setProperty(key, value, important ? 'important' : '');
        }
    }
    function select_option(select, value) {
        for (let i = 0; i < select.options.length; i += 1) {
            const option = select.options[i];
            if (option.__value === value) {
                option.selected = true;
                return;
            }
        }
        select.selectedIndex = -1; // no option should be selected
    }
    function select_value(select) {
        const selected_option = select.querySelector(':checked') || select.options[0];
        return selected_option && selected_option.__value;
    }
    // unfortunately this can't be a constant as that wouldn't be tree-shakeable
    // so we cache the result instead
    let crossorigin;
    function is_crossorigin() {
        if (crossorigin === undefined) {
            crossorigin = false;
            try {
                if (typeof window !== 'undefined' && window.parent) {
                    void window.parent.document;
                }
            }
            catch (error) {
                crossorigin = true;
            }
        }
        return crossorigin;
    }
    function add_resize_listener(node, fn) {
        const computed_style = getComputedStyle(node);
        if (computed_style.position === 'static') {
            node.style.position = 'relative';
        }
        const iframe = element('iframe');
        iframe.setAttribute('style', 'display: block; position: absolute; top: 0; left: 0; width: 100%; height: 100%; ' +
            'overflow: hidden; border: 0; opacity: 0; pointer-events: none; z-index: -1;');
        iframe.setAttribute('aria-hidden', 'true');
        iframe.tabIndex = -1;
        const crossorigin = is_crossorigin();
        let unsubscribe;
        if (crossorigin) {
            iframe.src = "data:text/html,<script>onresize=function(){parent.postMessage(0,'*')}</script>";
            unsubscribe = listen(window, 'message', (event) => {
                if (event.source === iframe.contentWindow)
                    fn();
            });
        }
        else {
            iframe.src = 'about:blank';
            iframe.onload = () => {
                unsubscribe = listen(iframe.contentWindow, 'resize', fn);
            };
        }
        append(node, iframe);
        return () => {
            if (crossorigin) {
                unsubscribe();
            }
            else if (unsubscribe && iframe.contentWindow) {
                unsubscribe();
            }
            detach(iframe);
        };
    }
    function toggle_class(element, name, toggle) {
        element.classList[toggle ? 'add' : 'remove'](name);
    }
    function custom_event(type, detail, { bubbles = false, cancelable = false } = {}) {
        const e = document.createEvent('CustomEvent');
        e.initCustomEvent(type, bubbles, cancelable, detail);
        return e;
    }
    class HtmlTag {
        constructor(is_svg = false) {
            this.is_svg = false;
            this.is_svg = is_svg;
            this.e = this.n = null;
        }
        c(html) {
            this.h(html);
        }
        m(html, target, anchor = null) {
            if (!this.e) {
                if (this.is_svg)
                    this.e = svg_element(target.nodeName);
                else
                    this.e = element(target.nodeName);
                this.t = target;
                this.c(html);
            }
            this.i(anchor);
        }
        h(html) {
            this.e.innerHTML = html;
            this.n = Array.from(this.e.childNodes);
        }
        i(anchor) {
            for (let i = 0; i < this.n.length; i += 1) {
                insert(this.t, this.n[i], anchor);
            }
        }
        p(html) {
            this.d();
            this.h(html);
            this.i(this.a);
        }
        d() {
            this.n.forEach(detach);
        }
    }

    // we need to store the information for multiple documents because a Svelte application could also contain iframes
    // https://github.com/sveltejs/svelte/issues/3624
    const managed_styles = new Map();
    let active$1 = 0;
    // https://github.com/darkskyapp/string-hash/blob/master/index.js
    function hash(str) {
        let hash = 5381;
        let i = str.length;
        while (i--)
            hash = ((hash << 5) - hash) ^ str.charCodeAt(i);
        return hash >>> 0;
    }
    function create_style_information(doc, node) {
        const info = { stylesheet: append_empty_stylesheet(node), rules: {} };
        managed_styles.set(doc, info);
        return info;
    }
    function create_rule(node, a, b, duration, delay, ease, fn, uid = 0) {
        const step = 16.666 / duration;
        let keyframes = '{\n';
        for (let p = 0; p <= 1; p += step) {
            const t = a + (b - a) * ease(p);
            keyframes += p * 100 + `%{${fn(t, 1 - t)}}\n`;
        }
        const rule = keyframes + `100% {${fn(b, 1 - b)}}\n}`;
        const name = `__svelte_${hash(rule)}_${uid}`;
        const doc = get_root_for_style(node);
        const { stylesheet, rules } = managed_styles.get(doc) || create_style_information(doc, node);
        if (!rules[name]) {
            rules[name] = true;
            stylesheet.insertRule(`@keyframes ${name} ${rule}`, stylesheet.cssRules.length);
        }
        const animation = node.style.animation || '';
        node.style.animation = `${animation ? `${animation}, ` : ''}${name} ${duration}ms linear ${delay}ms 1 both`;
        active$1 += 1;
        return name;
    }
    function delete_rule(node, name) {
        const previous = (node.style.animation || '').split(', ');
        const next = previous.filter(name
            ? anim => anim.indexOf(name) < 0 // remove specific animation
            : anim => anim.indexOf('__svelte') === -1 // remove all Svelte animations
        );
        const deleted = previous.length - next.length;
        if (deleted) {
            node.style.animation = next.join(', ');
            active$1 -= deleted;
            if (!active$1)
                clear_rules();
        }
    }
    function clear_rules() {
        raf(() => {
            if (active$1)
                return;
            managed_styles.forEach(info => {
                const { stylesheet } = info;
                let i = stylesheet.cssRules.length;
                while (i--)
                    stylesheet.deleteRule(i);
                info.rules = {};
            });
            managed_styles.clear();
        });
    }

    let current_component;
    function set_current_component(component) {
        current_component = component;
    }
    function get_current_component() {
        if (!current_component)
            throw new Error('Function called outside component initialization');
        return current_component;
    }
    function onMount(fn) {
        get_current_component().$$.on_mount.push(fn);
    }
    function afterUpdate(fn) {
        get_current_component().$$.after_update.push(fn);
    }
    function onDestroy(fn) {
        get_current_component().$$.on_destroy.push(fn);
    }
    function createEventDispatcher() {
        const component = get_current_component();
        return (type, detail, { cancelable = false } = {}) => {
            const callbacks = component.$$.callbacks[type];
            if (callbacks) {
                // TODO are there situations where events could be dispatched
                // in a server (non-DOM) environment?
                const event = custom_event(type, detail, { cancelable });
                callbacks.slice().forEach(fn => {
                    fn.call(component, event);
                });
                return !event.defaultPrevented;
            }
            return true;
        };
    }
    function setContext(key, context) {
        get_current_component().$$.context.set(key, context);
        return context;
    }
    function getContext(key) {
        return get_current_component().$$.context.get(key);
    }
    function hasContext(key) {
        return get_current_component().$$.context.has(key);
    }
    // TODO figure out if we still want to support
    // shorthand events, or if we want to implement
    // a real bubbling mechanism
    function bubble(component, event) {
        const callbacks = component.$$.callbacks[event.type];
        if (callbacks) {
            // @ts-ignore
            callbacks.slice().forEach(fn => fn.call(this, event));
        }
    }

    const dirty_components = [];
    const binding_callbacks = [];
    const render_callbacks = [];
    const flush_callbacks = [];
    const resolved_promise = Promise.resolve();
    let update_scheduled = false;
    function schedule_update() {
        if (!update_scheduled) {
            update_scheduled = true;
            resolved_promise.then(flush);
        }
    }
    function tick() {
        schedule_update();
        return resolved_promise;
    }
    function add_render_callback(fn) {
        render_callbacks.push(fn);
    }
    function add_flush_callback(fn) {
        flush_callbacks.push(fn);
    }
    // flush() calls callbacks in this order:
    // 1. All beforeUpdate callbacks, in order: parents before children
    // 2. All bind:this callbacks, in reverse order: children before parents.
    // 3. All afterUpdate callbacks, in order: parents before children. EXCEPT
    //    for afterUpdates called during the initial onMount, which are called in
    //    reverse order: children before parents.
    // Since callbacks might update component values, which could trigger another
    // call to flush(), the following steps guard against this:
    // 1. During beforeUpdate, any updated components will be added to the
    //    dirty_components array and will cause a reentrant call to flush(). Because
    //    the flush index is kept outside the function, the reentrant call will pick
    //    up where the earlier call left off and go through all dirty components. The
    //    current_component value is saved and restored so that the reentrant call will
    //    not interfere with the "parent" flush() call.
    // 2. bind:this callbacks cannot trigger new flush() calls.
    // 3. During afterUpdate, any updated components will NOT have their afterUpdate
    //    callback called a second time; the seen_callbacks set, outside the flush()
    //    function, guarantees this behavior.
    const seen_callbacks = new Set();
    let flushidx = 0; // Do *not* move this inside the flush() function
    function flush() {
        const saved_component = current_component;
        do {
            // first, call beforeUpdate functions
            // and update components
            while (flushidx < dirty_components.length) {
                const component = dirty_components[flushidx];
                flushidx++;
                set_current_component(component);
                update(component.$$);
            }
            set_current_component(null);
            dirty_components.length = 0;
            flushidx = 0;
            while (binding_callbacks.length)
                binding_callbacks.pop()();
            // then, once components are updated, call
            // afterUpdate functions. This may cause
            // subsequent updates...
            for (let i = 0; i < render_callbacks.length; i += 1) {
                const callback = render_callbacks[i];
                if (!seen_callbacks.has(callback)) {
                    // ...so guard against infinite loops
                    seen_callbacks.add(callback);
                    callback();
                }
            }
            render_callbacks.length = 0;
        } while (dirty_components.length);
        while (flush_callbacks.length) {
            flush_callbacks.pop()();
        }
        update_scheduled = false;
        seen_callbacks.clear();
        set_current_component(saved_component);
    }
    function update($$) {
        if ($$.fragment !== null) {
            $$.update();
            run_all($$.before_update);
            const dirty = $$.dirty;
            $$.dirty = [-1];
            $$.fragment && $$.fragment.p($$.ctx, dirty);
            $$.after_update.forEach(add_render_callback);
        }
    }

    let promise;
    function wait() {
        if (!promise) {
            promise = Promise.resolve();
            promise.then(() => {
                promise = null;
            });
        }
        return promise;
    }
    function dispatch(node, direction, kind) {
        node.dispatchEvent(custom_event(`${direction ? 'intro' : 'outro'}${kind}`));
    }
    const outroing = new Set();
    let outros;
    function group_outros() {
        outros = {
            r: 0,
            c: [],
            p: outros // parent group
        };
    }
    function check_outros() {
        if (!outros.r) {
            run_all(outros.c);
        }
        outros = outros.p;
    }
    function transition_in(block, local) {
        if (block && block.i) {
            outroing.delete(block);
            block.i(local);
        }
    }
    function transition_out(block, local, detach, callback) {
        if (block && block.o) {
            if (outroing.has(block))
                return;
            outroing.add(block);
            outros.c.push(() => {
                outroing.delete(block);
                if (callback) {
                    if (detach)
                        block.d(1);
                    callback();
                }
            });
            block.o(local);
        }
        else if (callback) {
            callback();
        }
    }
    const null_transition = { duration: 0 };
    function create_bidirectional_transition(node, fn, params, intro) {
        let config = fn(node, params);
        let t = intro ? 0 : 1;
        let running_program = null;
        let pending_program = null;
        let animation_name = null;
        function clear_animation() {
            if (animation_name)
                delete_rule(node, animation_name);
        }
        function init(program, duration) {
            const d = (program.b - t);
            duration *= Math.abs(d);
            return {
                a: t,
                b: program.b,
                d,
                duration,
                start: program.start,
                end: program.start + duration,
                group: program.group
            };
        }
        function go(b) {
            const { delay = 0, duration = 300, easing = identity, tick = noop, css } = config || null_transition;
            const program = {
                start: now() + delay,
                b
            };
            if (!b) {
                // @ts-ignore todo: improve typings
                program.group = outros;
                outros.r += 1;
            }
            if (running_program || pending_program) {
                pending_program = program;
            }
            else {
                // if this is an intro, and there's a delay, we need to do
                // an initial tick and/or apply CSS animation immediately
                if (css) {
                    clear_animation();
                    animation_name = create_rule(node, t, b, duration, delay, easing, css);
                }
                if (b)
                    tick(0, 1);
                running_program = init(program, duration);
                add_render_callback(() => dispatch(node, b, 'start'));
                loop(now => {
                    if (pending_program && now > pending_program.start) {
                        running_program = init(pending_program, duration);
                        pending_program = null;
                        dispatch(node, running_program.b, 'start');
                        if (css) {
                            clear_animation();
                            animation_name = create_rule(node, t, running_program.b, running_program.duration, 0, easing, config.css);
                        }
                    }
                    if (running_program) {
                        if (now >= running_program.end) {
                            tick(t = running_program.b, 1 - t);
                            dispatch(node, running_program.b, 'end');
                            if (!pending_program) {
                                // we're done
                                if (running_program.b) {
                                    // intro — we can tidy up immediately
                                    clear_animation();
                                }
                                else {
                                    // outro — needs to be coordinated
                                    if (!--running_program.group.r)
                                        run_all(running_program.group.c);
                                }
                            }
                            running_program = null;
                        }
                        else if (now >= running_program.start) {
                            const p = now - running_program.start;
                            t = running_program.a + running_program.d * easing(p / running_program.duration);
                            tick(t, 1 - t);
                        }
                    }
                    return !!(running_program || pending_program);
                });
            }
        }
        return {
            run(b) {
                if (is_function(config)) {
                    wait().then(() => {
                        // @ts-ignore
                        config = config();
                        go(b);
                    });
                }
                else {
                    go(b);
                }
            },
            end() {
                clear_animation();
                running_program = pending_program = null;
            }
        };
    }

    function handle_promise(promise, info) {
        const token = info.token = {};
        function update(type, index, key, value) {
            if (info.token !== token)
                return;
            info.resolved = value;
            let child_ctx = info.ctx;
            if (key !== undefined) {
                child_ctx = child_ctx.slice();
                child_ctx[key] = value;
            }
            const block = type && (info.current = type)(child_ctx);
            let needs_flush = false;
            if (info.block) {
                if (info.blocks) {
                    info.blocks.forEach((block, i) => {
                        if (i !== index && block) {
                            group_outros();
                            transition_out(block, 1, 1, () => {
                                if (info.blocks[i] === block) {
                                    info.blocks[i] = null;
                                }
                            });
                            check_outros();
                        }
                    });
                }
                else {
                    info.block.d(1);
                }
                block.c();
                transition_in(block, 1);
                block.m(info.mount(), info.anchor);
                needs_flush = true;
            }
            info.block = block;
            if (info.blocks)
                info.blocks[index] = block;
            if (needs_flush) {
                flush();
            }
        }
        if (is_promise(promise)) {
            const current_component = get_current_component();
            promise.then(value => {
                set_current_component(current_component);
                update(info.then, 1, info.value, value);
                set_current_component(null);
            }, error => {
                set_current_component(current_component);
                update(info.catch, 2, info.error, error);
                set_current_component(null);
                if (!info.hasCatch) {
                    throw error;
                }
            });
            // if we previously had a then/catch block, destroy it
            if (info.current !== info.pending) {
                update(info.pending, 0);
                return true;
            }
        }
        else {
            if (info.current !== info.then) {
                update(info.then, 1, info.value, promise);
                return true;
            }
            info.resolved = promise;
        }
    }
    function update_await_block_branch(info, ctx, dirty) {
        const child_ctx = ctx.slice();
        const { resolved } = info;
        if (info.current === info.then) {
            child_ctx[info.value] = resolved;
        }
        if (info.current === info.catch) {
            child_ctx[info.error] = resolved;
        }
        info.block.p(child_ctx, dirty);
    }

    const globals = (typeof window !== 'undefined'
        ? window
        : typeof globalThis !== 'undefined'
            ? globalThis
            : global);
    function outro_and_destroy_block(block, lookup) {
        transition_out(block, 1, 1, () => {
            lookup.delete(block.key);
        });
    }
    function update_keyed_each(old_blocks, dirty, get_key, dynamic, ctx, list, lookup, node, destroy, create_each_block, next, get_context) {
        let o = old_blocks.length;
        let n = list.length;
        let i = o;
        const old_indexes = {};
        while (i--)
            old_indexes[old_blocks[i].key] = i;
        const new_blocks = [];
        const new_lookup = new Map();
        const deltas = new Map();
        i = n;
        while (i--) {
            const child_ctx = get_context(ctx, list, i);
            const key = get_key(child_ctx);
            let block = lookup.get(key);
            if (!block) {
                block = create_each_block(key, child_ctx);
                block.c();
            }
            else if (dynamic) {
                block.p(child_ctx, dirty);
            }
            new_lookup.set(key, new_blocks[i] = block);
            if (key in old_indexes)
                deltas.set(key, Math.abs(i - old_indexes[key]));
        }
        const will_move = new Set();
        const did_move = new Set();
        function insert(block) {
            transition_in(block, 1);
            block.m(node, next);
            lookup.set(block.key, block);
            next = block.first;
            n--;
        }
        while (o && n) {
            const new_block = new_blocks[n - 1];
            const old_block = old_blocks[o - 1];
            const new_key = new_block.key;
            const old_key = old_block.key;
            if (new_block === old_block) {
                // do nothing
                next = new_block.first;
                o--;
                n--;
            }
            else if (!new_lookup.has(old_key)) {
                // remove old block
                destroy(old_block, lookup);
                o--;
            }
            else if (!lookup.has(new_key) || will_move.has(new_key)) {
                insert(new_block);
            }
            else if (did_move.has(old_key)) {
                o--;
            }
            else if (deltas.get(new_key) > deltas.get(old_key)) {
                did_move.add(new_key);
                insert(new_block);
            }
            else {
                will_move.add(old_key);
                o--;
            }
        }
        while (o--) {
            const old_block = old_blocks[o];
            if (!new_lookup.has(old_block.key))
                destroy(old_block, lookup);
        }
        while (n)
            insert(new_blocks[n - 1]);
        return new_blocks;
    }
    function validate_each_keys(ctx, list, get_context, get_key) {
        const keys = new Set();
        for (let i = 0; i < list.length; i++) {
            const key = get_key(get_context(ctx, list, i));
            if (keys.has(key)) {
                throw new Error('Cannot have duplicate keys in a keyed each');
            }
            keys.add(key);
        }
    }

    function get_spread_update(levels, updates) {
        const update = {};
        const to_null_out = {};
        const accounted_for = { $$scope: 1 };
        let i = levels.length;
        while (i--) {
            const o = levels[i];
            const n = updates[i];
            if (n) {
                for (const key in o) {
                    if (!(key in n))
                        to_null_out[key] = 1;
                }
                for (const key in n) {
                    if (!accounted_for[key]) {
                        update[key] = n[key];
                        accounted_for[key] = 1;
                    }
                }
                levels[i] = n;
            }
            else {
                for (const key in o) {
                    accounted_for[key] = 1;
                }
            }
        }
        for (const key in to_null_out) {
            if (!(key in update))
                update[key] = undefined;
        }
        return update;
    }
    function get_spread_object(spread_props) {
        return typeof spread_props === 'object' && spread_props !== null ? spread_props : {};
    }

    function bind(component, name, callback) {
        const index = component.$$.props[name];
        if (index !== undefined) {
            component.$$.bound[index] = callback;
            callback(component.$$.ctx[index]);
        }
    }
    function create_component(block) {
        block && block.c();
    }
    function mount_component(component, target, anchor, customElement) {
        const { fragment, on_mount, on_destroy, after_update } = component.$$;
        fragment && fragment.m(target, anchor);
        if (!customElement) {
            // onMount happens before the initial afterUpdate
            add_render_callback(() => {
                const new_on_destroy = on_mount.map(run).filter(is_function);
                if (on_destroy) {
                    on_destroy.push(...new_on_destroy);
                }
                else {
                    // Edge case - component was destroyed immediately,
                    // most likely as a result of a binding initialising
                    run_all(new_on_destroy);
                }
                component.$$.on_mount = [];
            });
        }
        after_update.forEach(add_render_callback);
    }
    function destroy_component(component, detaching) {
        const $$ = component.$$;
        if ($$.fragment !== null) {
            run_all($$.on_destroy);
            $$.fragment && $$.fragment.d(detaching);
            // TODO null out other refs, including component.$$ (but need to
            // preserve final state?)
            $$.on_destroy = $$.fragment = null;
            $$.ctx = [];
        }
    }
    function make_dirty(component, i) {
        if (component.$$.dirty[0] === -1) {
            dirty_components.push(component);
            schedule_update();
            component.$$.dirty.fill(0);
        }
        component.$$.dirty[(i / 31) | 0] |= (1 << (i % 31));
    }
    function init(component, options, instance, create_fragment, not_equal, props, append_styles, dirty = [-1]) {
        const parent_component = current_component;
        set_current_component(component);
        const $$ = component.$$ = {
            fragment: null,
            ctx: null,
            // state
            props,
            update: noop,
            not_equal,
            bound: blank_object(),
            // lifecycle
            on_mount: [],
            on_destroy: [],
            on_disconnect: [],
            before_update: [],
            after_update: [],
            context: new Map(options.context || (parent_component ? parent_component.$$.context : [])),
            // everything else
            callbacks: blank_object(),
            dirty,
            skip_bound: false,
            root: options.target || parent_component.$$.root
        };
        append_styles && append_styles($$.root);
        let ready = false;
        $$.ctx = instance
            ? instance(component, options.props || {}, (i, ret, ...rest) => {
                const value = rest.length ? rest[0] : ret;
                if ($$.ctx && not_equal($$.ctx[i], $$.ctx[i] = value)) {
                    if (!$$.skip_bound && $$.bound[i])
                        $$.bound[i](value);
                    if (ready)
                        make_dirty(component, i);
                }
                return ret;
            })
            : [];
        $$.update();
        ready = true;
        run_all($$.before_update);
        // `false` as a special case of no DOM component
        $$.fragment = create_fragment ? create_fragment($$.ctx) : false;
        if (options.target) {
            if (options.hydrate) {
                const nodes = children(options.target);
                // eslint-disable-next-line @typescript-eslint/no-non-null-assertion
                $$.fragment && $$.fragment.l(nodes);
                nodes.forEach(detach);
            }
            else {
                // eslint-disable-next-line @typescript-eslint/no-non-null-assertion
                $$.fragment && $$.fragment.c();
            }
            if (options.intro)
                transition_in(component.$$.fragment);
            mount_component(component, options.target, options.anchor, options.customElement);
            flush();
        }
        set_current_component(parent_component);
    }
    /**
     * Base class for Svelte components. Used when dev=false.
     */
    class SvelteComponent {
        $destroy() {
            destroy_component(this, 1);
            this.$destroy = noop;
        }
        $on(type, callback) {
            const callbacks = (this.$$.callbacks[type] || (this.$$.callbacks[type] = []));
            callbacks.push(callback);
            return () => {
                const index = callbacks.indexOf(callback);
                if (index !== -1)
                    callbacks.splice(index, 1);
            };
        }
        $set($$props) {
            if (this.$$set && !is_empty($$props)) {
                this.$$.skip_bound = true;
                this.$$set($$props);
                this.$$.skip_bound = false;
            }
        }
    }

    function dispatch_dev(type, detail) {
        document.dispatchEvent(custom_event(type, Object.assign({ version: '3.49.0' }, detail), { bubbles: true }));
    }
    function append_dev(target, node) {
        dispatch_dev('SvelteDOMInsert', { target, node });
        append(target, node);
    }
    function insert_dev(target, node, anchor) {
        dispatch_dev('SvelteDOMInsert', { target, node, anchor });
        insert(target, node, anchor);
    }
    function detach_dev(node) {
        dispatch_dev('SvelteDOMRemove', { node });
        detach(node);
    }
    function listen_dev(node, event, handler, options, has_prevent_default, has_stop_propagation) {
        const modifiers = options === true ? ['capture'] : options ? Array.from(Object.keys(options)) : [];
        if (has_prevent_default)
            modifiers.push('preventDefault');
        if (has_stop_propagation)
            modifiers.push('stopPropagation');
        dispatch_dev('SvelteDOMAddEventListener', { node, event, handler, modifiers });
        const dispose = listen(node, event, handler, options);
        return () => {
            dispatch_dev('SvelteDOMRemoveEventListener', { node, event, handler, modifiers });
            dispose();
        };
    }
    function attr_dev(node, attribute, value) {
        attr(node, attribute, value);
        if (value == null)
            dispatch_dev('SvelteDOMRemoveAttribute', { node, attribute });
        else
            dispatch_dev('SvelteDOMSetAttribute', { node, attribute, value });
    }
    function prop_dev(node, property, value) {
        node[property] = value;
        dispatch_dev('SvelteDOMSetProperty', { node, property, value });
    }
    function set_data_dev(text, data) {
        data = '' + data;
        if (text.wholeText === data)
            return;
        dispatch_dev('SvelteDOMSetData', { node: text, data });
        text.data = data;
    }
    function validate_each_argument(arg) {
        if (typeof arg !== 'string' && !(arg && typeof arg === 'object' && 'length' in arg)) {
            let msg = '{#each} only iterates over array-like objects.';
            if (typeof Symbol === 'function' && arg && Symbol.iterator in arg) {
                msg += ' You can use a spread to convert this iterable into an array.';
            }
            throw new Error(msg);
        }
    }
    function validate_slots(name, slot, keys) {
        for (const slot_key of Object.keys(slot)) {
            if (!~keys.indexOf(slot_key)) {
                console.warn(`<${name}> received an unexpected slot "${slot_key}".`);
            }
        }
    }
    /**
     * Base class for Svelte components with some minor dev-enhancements. Used when dev=true.
     */
    class SvelteComponentDev extends SvelteComponent {
        constructor(options) {
            if (!options || (!options.target && !options.$$inline)) {
                throw new Error("'target' is a required option");
            }
            super();
        }
        $destroy() {
            super.$destroy();
            this.$destroy = () => {
                console.warn('Component was already destroyed'); // eslint-disable-line no-console
            };
        }
        $capture_state() { }
        $inject_state() { }
    }

    const subscriber_queue = [];
    /**
     * Creates a `Readable` store that allows reading by subscription.
     * @param value initial value
     * @param {StartStopNotifier}start start and stop notifications for subscriptions
     */
    function readable(value, start) {
        return {
            subscribe: writable(value, start).subscribe
        };
    }
    /**
     * Create a `Writable` store that allows both updating and reading by subscription.
     * @param {*=}value initial value
     * @param {StartStopNotifier=}start start and stop notifications for subscriptions
     */
    function writable(value, start = noop) {
        let stop;
        const subscribers = new Set();
        function set(new_value) {
            if (safe_not_equal(value, new_value)) {
                value = new_value;
                if (stop) { // store is ready
                    const run_queue = !subscriber_queue.length;
                    for (const subscriber of subscribers) {
                        subscriber[1]();
                        subscriber_queue.push(subscriber, value);
                    }
                    if (run_queue) {
                        for (let i = 0; i < subscriber_queue.length; i += 2) {
                            subscriber_queue[i][0](subscriber_queue[i + 1]);
                        }
                        subscriber_queue.length = 0;
                    }
                }
            }
        }
        function update(fn) {
            set(fn(value));
        }
        function subscribe(run, invalidate = noop) {
            const subscriber = [run, invalidate];
            subscribers.add(subscriber);
            if (subscribers.size === 1) {
                stop = start(set) || noop;
            }
            run(value);
            return () => {
                subscribers.delete(subscriber);
                if (subscribers.size === 0) {
                    stop();
                    stop = null;
                }
            };
        }
        return { set, update, subscribe };
    }
    function derived(stores, fn, initial_value) {
        const single = !Array.isArray(stores);
        const stores_array = single
            ? [stores]
            : stores;
        const auto = fn.length < 2;
        return readable(initial_value, (set) => {
            let inited = false;
            const values = [];
            let pending = 0;
            let cleanup = noop;
            const sync = () => {
                if (pending) {
                    return;
                }
                cleanup();
                const result = fn(single ? values[0] : values, set);
                if (auto) {
                    set(result);
                }
                else {
                    cleanup = is_function(result) ? result : noop;
                }
            };
            const unsubscribers = stores_array.map((store, i) => subscribe(store, (value) => {
                values[i] = value;
                pending &= ~(1 << i);
                if (inited) {
                    sync();
                }
            }, () => {
                pending |= (1 << i);
            }));
            inited = true;
            sync();
            return function stop() {
                run_all(unsubscribers);
                cleanup();
            };
        });
    }

    /**
     * Does the current object have different keys or values compared to the previous version?
     *
     * @param {object} previous
     * @param {object} current
     *
     * @returns {boolean}
     */
    function objectsDiffer( [previous, current] ) {
    	if ( !previous || !current ) {
    		return false;
    	}

    	// Any difference in keys?
    	const prevKeys = Object.keys( previous );
    	const currKeys = Object.keys( current );

    	if ( prevKeys.length !== currKeys.length ) {
    		return true;
    	}

    	// Symmetrical diff to find extra keys in either object.
    	if (
    		prevKeys.filter( x => !currKeys.includes( x ) )
    			.concat(
    				currKeys.filter( x => !prevKeys.includes( x ) )
    			)
    			.length > 0
    	) {
    		return true;
    	}

    	// Any difference in values?
    	for ( const key in previous ) {
    		if ( JSON.stringify( current[ key ] ) !== JSON.stringify( previous[ key ] ) ) {
    			return true;
    		}
    	}

    	return false;
    }

    // Initial config store.
    const config = writable( {} );

    // Whether settings are locked due to background activity such as upgrade.
    const settingsLocked = writable( false );

    // Convenience readable store of server's settings, derived from config.
    const current_settings = derived( config, $config => $config.settings );

    // Convenience readable store of defined settings keys, derived from config.
    const defined_settings = derived( config, $config => $config.defined_settings );

    // Convenience readable store of translated strings, derived from config.
    const strings = derived( config, $config => $config.strings );

    // Convenience readable store for nonce, derived from config.
    const nonce = derived( config, $config => $config.nonce );

    // Convenience readable store of urls, derived from config.
    const urls = derived( config, $config => $config.urls );

    // Convenience readable store of docs, derived from config.
    const docs = derived( config, $config => $config.docs );

    // Convenience readable store of api endpoints, derived from config.
    const endpoints = derived( config, $config => $config.endpoints );

    // Convenience readable store of diagnostics, derived from config.
    const diagnostics = derived( config, $config => $config.diagnostics );

    // Convenience readable store of counts, derived from config.
    const counts = derived( config, $config => $config.counts );

    // Convenience readable store of upgrades, derived from config.
    derived( config, $config => $config.upgrades );

    // Convenience readable store of whether plugin is set up, derived from config.
    const is_plugin_setup = derived( config, $config => $config.is_plugin_setup );

    // Convenience readable store of whether plugin is set up, including with credentials, derived from config.
    const is_plugin_setup_with_credentials = derived( config, $config => $config.is_plugin_setup_with_credentials );

    // Convenience readable store of whether storage provider needs access credentials, derived from config.
    const needs_access_keys = derived( config, $config => $config.needs_access_keys );

    // Convenience readable store of whether bucket is writable, derived from config.
    derived( config, $config => $config.bucket_writable );

    // Store of inline errors and warnings to be shown next to settings.
    // Format is a map using settings key for keys, values are an array of objects that can be used to instantiate a notification.
    const settings_notifications = writable( new Map() );

    // Store of validation errors for settings.
    // Format is a map using settings key for keys, values are strings containing validation error.
    const validationErrors = writable( new Map() );

    // Does the app need a page refresh to resolve conflicts?
    const needs_refresh = writable( false );

    // Various stores may call the API, and the api object uses some stores.
    // To avoid cyclic dependencies, we therefore co-locate the api object with the stores.
    // We also need to add its functions much later so that JSHint does not complain about using the stores too early.
    const api = {};

    /**
     * Creates store of settings.
     *
     * @return {Object}
     */
    function createSettings() {
    	const { subscribe, set, update } = writable( [] );

    	return {
    		subscribe,
    		set,
    		async save() {
    			const json = await api.put( "settings", get_store_value( this ) );

    			if ( json.hasOwnProperty( "saved" ) && true === json.saved ) {
    				// Sync settings with what the server has.
    				this.updateSettings( json );

    				return json;
    			}

    			return {};
    		},
    		reset() {
    			set( { ...get_store_value( current_settings ) } );
    		},
    		async fetch() {
    			const json = await api.get( "settings", {} );
    			this.updateSettings( json );
    		},
    		updateSettings( json ) {
    			if (
    				json.hasOwnProperty( "defined_settings" ) &&
    				json.hasOwnProperty( "settings" ) &&
    				json.hasOwnProperty( "storage_providers" ) &&
    				json.hasOwnProperty( "delivery_providers" ) &&
    				json.hasOwnProperty( "is_plugin_setup" ) &&
    				json.hasOwnProperty( "is_plugin_setup_with_credentials" ) &&
    				json.hasOwnProperty( "needs_access_keys" ) &&
    				json.hasOwnProperty( "bucket_writable" ) &&
    				json.hasOwnProperty( "urls" )
    			) {
    				// Update our understanding of what the server's settings are.
    				config.update( $config => {
    					return {
    						...$config,
    						defined_settings: json.defined_settings,
    						settings: json.settings,
    						storage_providers: json.storage_providers,
    						delivery_providers: json.delivery_providers,
    						is_plugin_setup: json.is_plugin_setup,
    						is_plugin_setup_with_credentials: json.is_plugin_setup_with_credentials,
    						needs_access_keys: json.needs_access_keys,
    						bucket_writable: json.bucket_writable,
    						urls: json.urls
    					};
    				} );
    				// Update our local working copy of the settings.
    				update( $settings => {
    					return { ...json.settings };
    				} );
    			}
    		}
    	};
    }

    const settings = createSettings();

    // Have the settings been changed from current server side settings?
    const settings_changed = derived( [settings, current_settings], objectsDiffer );

    // Convenience readable store of available storage providers.
    const storage_providers = derived( [config, urls], ( [$config, $urls] ) => {
    	for ( const key in $config.storage_providers ) {
    		$config.storage_providers[ key ].icon = $urls.assets + "img/icon/provider/storage/" + $config.storage_providers[ key ].provider_key_name + ".svg";
    		$config.storage_providers[ key ].link_icon = $urls.assets + "img/icon/provider/storage/" + $config.storage_providers[ key ].provider_key_name + "-link.svg";
    		$config.storage_providers[ key ].round_icon = $urls.assets + "img/icon/provider/storage/" + $config.storage_providers[ key ].provider_key_name + "-round.svg";
    	}

    	return $config.storage_providers;
    } );

    // Convenience readable store of storage provider's details.
    const storage_provider = derived( [settings, storage_providers], ( [$settings, $storage_providers] ) => {
    	if ( $settings.hasOwnProperty( "provider" ) && $storage_providers.hasOwnProperty( $settings.provider ) ) {
    		return $storage_providers[ $settings.provider ];
    	} else {
    		return [];
    	}
    } );

    // Convenience readable store of available delivery providers.
    const delivery_providers = derived( [config, urls, storage_provider], ( [$config, $urls, $storage_provider] ) => {
    	for ( const key in $config.delivery_providers ) {
    		if ( "storage" === key ) {
    			$config.delivery_providers[ key ].icon = $storage_provider.icon;
    			$config.delivery_providers[ key ].round_icon = $storage_provider.round_icon;
    			$config.delivery_providers[ key ].provider_service_quick_start_url = $storage_provider.provider_service_quick_start_url;
    		} else {
    			$config.delivery_providers[ key ].icon = $urls.assets + "img/icon/provider/delivery/" + $config.delivery_providers[ key ].provider_key_name + ".svg";
    			$config.delivery_providers[ key ].round_icon = $urls.assets + "img/icon/provider/delivery/" + $config.delivery_providers[ key ].provider_key_name + "-round.svg";
    		}
    	}

    	return $config.delivery_providers;
    } );

    // Convenience readable store of delivery provider's details.
    const delivery_provider = derived( [settings, delivery_providers, urls], ( [$settings, $delivery_providers, $urls] ) => {
    	if ( $settings.hasOwnProperty( "delivery-provider" ) && $delivery_providers.hasOwnProperty( $settings[ "delivery-provider" ] ) ) {
    		return $delivery_providers[ $settings[ "delivery-provider" ] ];
    	} else {
    		return [];
    	}
    } );

    // Full name for current region.
    const region_name = derived( [settings, storage_provider, strings], ( [$settings, $storage_provider, $strings] ) => {
    	if ( $settings.region && $storage_provider.regions && $storage_provider.regions.hasOwnProperty( $settings.region ) ) {
    		return $storage_provider.regions[ $settings.region ];
    	} else if ( $settings.region && $storage_provider.regions ) {
    		// Region set but not available in list of regions.
    		return $strings.unknown;
    	} else if ( $storage_provider.default_region && $storage_provider.regions && $storage_provider.regions.hasOwnProperty( $storage_provider.default_region ) ) {
    		// Region not set but default available.
    		return $storage_provider.regions[ $storage_provider.default_region ];
    	} else {
    		// Possibly no default region or regions available.
    		return $strings.unknown;
    	}
    } );

    // Convenience readable store of whether Block All Public Access is enabled.
    const bapa = derived( [settings, storage_provider], ( [$settings, $storage_provider] ) => {
    	return $storage_provider.block_public_access_supported && $settings.hasOwnProperty( "block-public-access" ) && $settings[ "block-public-access" ];
    } );

    // Convenience readable store of whether Object Ownership is enforced.
    const ooe = derived( [settings, storage_provider], ( [$settings, $storage_provider] ) => {
    	return $storage_provider.object_ownership_supported && $settings.hasOwnProperty( "object-ownership-enforced" ) && $settings[ "object-ownership-enforced" ];
    } );

    /**
     * Creates a store of notifications.
     *
     * Example object in the array:
     * {
     * 	id: "error-message",
     * 	type: "error", // error | warning | success | primary (default)
     * 	dismissible: true,
     * 	flash: true, // Optional, means notification is context specific and will not persist on server, defaults to true.
     * 	inline: false, // Optional, unlikely to be true, included here for completeness.
     * 	only_show_on_tab: "media-library", // Optional, blank/missing means on all tabs.
     * 	heading: "Global Error: Something has gone terribly pear shaped.", // Optional.
     * 	message: "We're so sorry, but unfortunately we're going to have to delete the year 2020.", // Optional.
     * 	icon: "notification-error.svg", // Optional icon file name to be shown in front of heading.
     * 	plainHeading: false, // Optional boolean as to whether a <p> tag should be used instead of <h3> for heading content.
     * 	extra: "", // Optional extra content to be shown in paragraph below message.
     * 	links: [], // Optional list of links to be shown at bottom of notice.
     * },
     *
     * @return {Object}
     */
    function createNotifications() {
    	const { subscribe, set, update } = writable( [] );

    	return {
    		set,
    		subscribe,
    		add( notification ) {
    			// There's a slight difference between our notification's formatting and what WP uses.
    			if ( notification.hasOwnProperty( "type" ) && notification.type === "updated" ) {
    				notification.type = "success";
    			}
    			if ( notification.hasOwnProperty( "type" ) && notification.type === "notice-warning" ) {
    				notification.type = "warning";
    			}
    			if ( notification.hasOwnProperty( "type" ) && notification.type === "notice-info" ) {
    				notification.type = "info";
    			}
    			if (
    				notification.hasOwnProperty( "message" ) &&
    				(!notification.hasOwnProperty( "heading" ) || notification.heading.trim().length === 0)
    			) {
    				notification.heading = notification.message;
    				notification.plainHeading = true;
    				delete notification.message;
    			}
    			if ( !notification.hasOwnProperty( "flash" ) ) {
    				notification.flash = true;
    			}

    			// We need some sort of id for indexing and to ensure rendering is efficient.
    			if ( !notification.hasOwnProperty( "id" ) ) {
    				// Notifications are useless without at least a heading or message, so we can be sure at least one exists.
    				const idHeading = notification.hasOwnProperty( "heading" ) ? notification.heading.trim() : "dynamic-heading";
    				const idMessage = notification.hasOwnProperty( "message" ) ? notification.message.trim() : "dynamic-message";

    				notification.id = btoa( idHeading + idMessage );
    			}

    			// So that rendering is efficient, but updates displayed notifications that re-use keys,
    			// we create a render_key based on id and created_at as created_at is churned on re-use.
    			const createdAt = notification.hasOwnProperty( "created_at" ) ? notification.created_at : 0;
    			notification.render_key = notification.id + "-" + createdAt;

    			update( $notifications => {
    				// Maybe update a notification if id already exists.
    				let index = -1;
    				if ( notification.hasOwnProperty( "id" ) ) {
    					index = $notifications.findIndex( _notification => _notification.id === notification.id );
    				}

    				if ( index >= 0 ) {
    					// If the id exists but has been dismissed, add the replacement notification to the end of the array
    					// if given notification is newer, otherwise skip it entirely.
    					if ( $notifications[ index ].hasOwnProperty( "dismissed" ) ) {
    						if ( $notifications[ index ].dismissed < notification.created_at ) {
    							$notifications.push( notification );
    							$notifications.splice( index, 1 );
    						}
    					} else {
    						// Update existing.
    						$notifications.splice( index, 1, notification );
    					}
    				} else {
    					// Add new.
    					$notifications.push( notification );
    				}

    				return $notifications.sort( this.sortCompare );
    			} );
    		},
    		sortCompare( a, b ) {
    			// Sort by created_at in case an existing notification was updated.
    			if ( a.created_at < b.created_at ) {
    				return -1;
    			}

    			if ( a.created_at > b.created_at ) {
    				return 1;
    			}

    			return 0;
    		},
    		async dismiss( id ) {
    			update( $notifications => {
    				const index = $notifications.findIndex( notification => notification.id === id );

    				// If the notification still exists, set a "dismissed" tombstone with the created_at value.
    				// The cleanup will delete any notifications that have been dismissed and no longer exist
    				// in the list of notifications retrieved from the server.
    				// The created_at value ensures that if a notification is retrieved from the server that
    				// has the same id but later created_at, then it can be added, otherwise it is skipped.
    				if ( index >= 0 ) {
    					if ( $notifications[ index ].hasOwnProperty( "created_at" ) ) {
    						$notifications[ index ].dismissed = $notifications[ index ].created_at;
    					} else {
    						// Notification likely did not come from server, maybe a local "flash" notification.
    						$notifications.splice( index, 1 );
    					}
    				}

    				return $notifications;
    			} );

    			// Tell server to dismiss notification, still ok to try if flash notification, makes sure it is definitely removed.
    			await api.delete( "notifications", { id: id, all_tabs: true } );
    		},
    		/**
    		 * Delete removes a notification from the UI without telling the server.
    		 */
    		delete( id ) {
    			update( $notifications => {
    				const index = $notifications.findIndex( notification => notification.id === id );

    				if ( index >= 0 ) {
    					$notifications.splice( index, 1 );
    				}

    				return $notifications;
    			} );
    		},
    		cleanup( latest ) {
    			update( $notifications => {
    				for ( const [index, notification] of $notifications.entries() ) {
    					// Only clean up dismissed or server created notices that no longer exist.
    					if ( notification.hasOwnProperty( "dismissed" ) || notification.hasOwnProperty( "created_at" ) ) {
    						const latestIndex = latest.findIndex( _notification => _notification.id === notification.id );

    						// If server doesn't know about the notification anymore, remove it.
    						if ( latestIndex < 0 ) {
    							$notifications.splice( index, 1 );
    						}
    					}
    				}

    				return $notifications;
    			} );
    		}
    	};
    }

    const notifications = createNotifications();

    // Controller for periodic fetch of state info.
    let stateFetchInterval;
    let stateFetchIntervalStarted = false;
    let stateFetchIntervalPaused = false;

    // Store of functions to call before an update of state processes the result into config.
    const preStateUpdateCallbacks = writable( [] );

    // Store of functions to call after an update of state processes the result into config.
    const postStateUpdateCallbacks = writable( [] );

    /**
     * Store of functions to call when state info is updated, and actual API access methods.
     *
     * Functions are called after the returned state info has been used to update the config store.
     * Therefore, functions should only be added to the store if extra processing is required.
     * The functions should be asynchronous as they are part of the reactive chain and called with await.
     *
     * @return {Object}
     */
    function createState() {
    	const { subscribe, set, update } = writable( [] );

    	return {
    		subscribe,
    		set,
    		update,
    		async fetch() {
    			const json = await api.get( "state", {} );

    			// Abort controller is still a bit hit or miss, so we'll go old skool.
    			if ( stateFetchIntervalStarted && !stateFetchIntervalPaused ) {
    				this.updateState( json );
    			}
    		},
    		updateState( json ) {
    			for ( const callable of get_store_value( preStateUpdateCallbacks ) ) {
    				callable( json );
    			}

    			const dirty = get_store_value( settings_changed );
    			const previous_settings = { ...get_store_value( current_settings ) }; // cloned

    			config.update( $config => {
    				return { ...$config, ...json };
    			} );

    			// If the settings weren't changed before, they shouldn't be now.
    			if ( !dirty && get_store_value( settings_changed ) ) {
    				settings.reset();
    			}

    			// If settings are in middle of being changed when changes come in
    			// from server, reset to server version.
    			if ( dirty && objectsDiffer( [previous_settings, get_store_value( current_settings )] ) ) {
    				needs_refresh.update( $needs_refresh => true );
    				settings.reset();
    			}

    			for ( const callable of get_store_value( postStateUpdateCallbacks ) ) {
    				callable( json );
    			}
    		},
    		startPeriodicFetch() {
    			stateFetchIntervalStarted = true;
    			stateFetchIntervalPaused = false;

    			stateFetchInterval = setInterval( async () => {
    				await this.fetch();
    			}, 5000 );
    		},
    		stopPeriodicFetch() {
    			stateFetchIntervalStarted = false;
    			stateFetchIntervalPaused = false;

    			clearInterval( stateFetchInterval );
    		},
    		pausePeriodicFetch() {
    			if ( stateFetchIntervalStarted ) {
    				stateFetchIntervalPaused = true;
    				clearInterval( stateFetchInterval );
    			}
    		},
    		resumePeriodicFetch() {
    			stateFetchIntervalPaused = false;

    			if ( stateFetchIntervalStarted ) {
    				this.startPeriodicFetch();
    			}
    		}
    	};
    }

    const state = createState();

    // API functions added here to avoid JSHint errors.
    api.headers = () => {
    	return {
    		'Accept': 'application/json',
    		'Content-Type': 'application/json',
    		'X-WP-Nonce': get_store_value( nonce )
    	};
    };

    api.url = ( endpoint ) => {
    	return get_store_value( urls ).api + get_store_value( endpoints )[ endpoint ];
    };

    api.get = async ( endpoint, params ) => {
    	let url = new URL( api.url( endpoint ) );

    	const searchParams = new URLSearchParams( params );

    	searchParams.forEach( function( value, name ) {
    		url.searchParams.set( name, value );
    	} );

    	const response = await fetch( url.toString(), {
    		method: 'GET',
    		headers: api.headers()
    	} );
    	return response.json().then( json => {
    		json = api.check_response( json );
    		return json;
    	} );
    };

    api.post = async ( endpoint, body ) => {
    	const response = await fetch( api.url( endpoint ), {
    		method: 'POST',
    		headers: api.headers(),
    		body: JSON.stringify( body )
    	} );
    	return response.json().then( json => {
    		json = api.check_response( json );
    		return json;
    	} );
    };

    api.put = async ( endpoint, body ) => {
    	const response = await fetch( api.url( endpoint ), {
    		method: 'PUT',
    		headers: api.headers(),
    		body: JSON.stringify( body )
    	} );
    	return response.json().then( json => {
    		json = api.check_response( json );
    		return json;
    	} );
    };

    api.delete = async ( endpoint, body ) => {
    	const response = await fetch( api.url( endpoint ), {
    		method: 'DELETE',
    		headers: api.headers(),
    		body: JSON.stringify( body )
    	} );
    	return response.json().then( json => {
    		json = api.check_response( json );
    		return json;
    	} );
    };

    api.check_errors = ( json ) => {
    	if ( json.code && json.message ) {
    		notifications.add( {
    			id: json.code,
    			type: 'error',
    			dismissible: true,
    			heading: get_store_value( strings ).api_error_notice_heading,
    			message: json.message
    		} );

    		// Just in case resultant json is expanded into a store.
    		delete json.code;
    		delete json.message;
    	}

    	return json;
    };

    api.check_notifications = ( json ) => {
    	const _notifications = json.hasOwnProperty( "notifications" ) ? json.notifications : [];
    	if ( _notifications ) {
    		for ( const notification of _notifications ) {
    			notifications.add( notification );
    		}
    	}
    	notifications.cleanup( _notifications );

    	// Just in case resultant json is expanded into a store.
    	delete json.notifications;

    	return json;
    };

    api.check_response = ( json ) => {
    	json = api.check_notifications( json );
    	json = api.check_errors( json );

    	return json;
    };

    /**
     * @typedef {Object} WrappedComponent Object returned by the `wrap` method
     * @property {SvelteComponent} component - Component to load (this is always asynchronous)
     * @property {RoutePrecondition[]} [conditions] - Route pre-conditions to validate
     * @property {Object} [props] - Optional dictionary of static props
     * @property {Object} [userData] - Optional user data dictionary
     * @property {bool} _sveltesparouter - Internal flag; always set to true
     */

    /**
     * @callback AsyncSvelteComponent
     * @returns {Promise<SvelteComponent>} Returns a Promise that resolves with a Svelte component
     */

    /**
     * @callback RoutePrecondition
     * @param {RouteDetail} detail - Route detail object
     * @returns {boolean|Promise<boolean>} If the callback returns a false-y value, it's interpreted as the precondition failed, so it aborts loading the component (and won't process other pre-condition callbacks)
     */

    /**
     * @typedef {Object} WrapOptions Options object for the call to `wrap`
     * @property {SvelteComponent} [component] - Svelte component to load (this is incompatible with `asyncComponent`)
     * @property {AsyncSvelteComponent} [asyncComponent] - Function that returns a Promise that fulfills with a Svelte component (e.g. `{asyncComponent: () => import('Foo.svelte')}`)
     * @property {SvelteComponent} [loadingComponent] - Svelte component to be displayed while the async route is loading (as a placeholder); when unset or false-y, no component is shown while component
     * @property {object} [loadingParams] - Optional dictionary passed to the `loadingComponent` component as params (for an exported prop called `params`)
     * @property {object} [userData] - Optional object that will be passed to events such as `routeLoading`, `routeLoaded`, `conditionsFailed`
     * @property {object} [props] - Optional key-value dictionary of static props that will be passed to the component. The props are expanded with {...props}, so the key in the dictionary becomes the name of the prop.
     * @property {RoutePrecondition[]|RoutePrecondition} [conditions] - Route pre-conditions to add, which will be executed in order
     */

    /**
     * Wraps a component to enable multiple capabilities:
     * 1. Using dynamically-imported component, with (e.g. `{asyncComponent: () => import('Foo.svelte')}`), which also allows bundlers to do code-splitting.
     * 2. Adding route pre-conditions (e.g. `{conditions: [...]}`)
     * 3. Adding static props that are passed to the component
     * 4. Adding custom userData, which is passed to route events (e.g. route loaded events) or to route pre-conditions (e.g. `{userData: {foo: 'bar}}`)
     * 
     * @param {WrapOptions} args - Arguments object
     * @returns {WrappedComponent} Wrapped component
     */
    function wrap$1(args) {
        if (!args) {
            throw Error('Parameter args is required')
        }

        // We need to have one and only one of component and asyncComponent
        // This does a "XNOR"
        if (!args.component == !args.asyncComponent) {
            throw Error('One and only one of component and asyncComponent is required')
        }

        // If the component is not async, wrap it into a function returning a Promise
        if (args.component) {
            args.asyncComponent = () => Promise.resolve(args.component);
        }

        // Parameter asyncComponent and each item of conditions must be functions
        if (typeof args.asyncComponent != 'function') {
            throw Error('Parameter asyncComponent must be a function')
        }
        if (args.conditions) {
            // Ensure it's an array
            if (!Array.isArray(args.conditions)) {
                args.conditions = [args.conditions];
            }
            for (let i = 0; i < args.conditions.length; i++) {
                if (!args.conditions[i] || typeof args.conditions[i] != 'function') {
                    throw Error('Invalid parameter conditions[' + i + ']')
                }
            }
        }

        // Check if we have a placeholder component
        if (args.loadingComponent) {
            args.asyncComponent.loading = args.loadingComponent;
            args.asyncComponent.loadingParams = args.loadingParams || undefined;
        }

        // Returns an object that contains all the functions to execute too
        // The _sveltesparouter flag is to confirm the object was created by this router
        const obj = {
            component: args.asyncComponent,
            userData: args.userData,
            conditions: (args.conditions && args.conditions.length) ? args.conditions : undefined,
            props: (args.props && Object.keys(args.props).length) ? args.props : {},
            _sveltesparouter: true
        };

        return obj
    }

    /**
     * Creates store of default pages.
     *
     * Having a title means inclusion in main tabs.
     *
     * @return {Object}
     */
    function createPages() {
    	// NOTE: get() only resolves after initialization, hence arrow functions for getting titles.
    	const { subscribe, set, update } = writable( [] );

    	return {
    		subscribe,
    		set,
    		add( page ) {
    			update( $pages => {
    				return [...$pages, page]
    					.sort( ( a, b ) => {
    						return a.position - b.position;
    					} );
    			} );
    		},
    		withPrefix( prefix = null ) {
    			return get_store_value( this ).filter( ( page ) => {
    				return (prefix && page.route.startsWith( prefix )) || !prefix;
    			} );
    		},
    		routes( prefix = null ) {
    			let defaultComponent = null;
    			let defaultUserData = null;
    			const routes = new Map();

    			// If a page can be enabled/disabled, check whether it is enabled before displaying.
    			const conditions = [
    				( detail ) => {
    					if (
    						detail.hasOwnProperty( "userData" ) &&
    						detail.userData.hasOwnProperty( "page" ) &&
    						detail.userData.page.hasOwnProperty( "enabled" )
    					) {
    						return detail.userData.page.enabled();
    					}

    					return true;
    				}
    			];

    			for ( const page of this.withPrefix( prefix ) ) {
    				const userData = { page: page };

    				let route = page.route;

    				if ( prefix && route !== prefix + "/*" ) {
    					route = route.replace( prefix, "" );
    				}

    				routes.set( route, wrap$1( {
    					component: page.component,
    					userData: userData,
    					conditions: conditions
    				} ) );

    				if ( !defaultComponent && page.default ) {
    					defaultComponent = page.component;
    					defaultUserData = userData;
    				}
    			}

    			if ( defaultComponent ) {
    				routes.set( "*", wrap$1( {
    					component: defaultComponent,
    					userData: defaultUserData,
    					conditions: conditions
    				} ) );
    			}

    			return routes;
    		},
    		handleRouteEvent( detail ) {
    			if ( detail.hasOwnProperty( "event" ) ) {
    				if ( !detail.hasOwnProperty( "data" ) ) {
    					detail.data = {};
    				}

    				// Find the first page that wants to handle the event
    				// , but also let other pages see the event
    				// so they can set any initial state etc.
    				let route = false;
    				for ( const page of get_store_value( this ).values() ) {
    					if ( page.events && page.events[ detail.event ] && page.events[ detail.event ]( detail.data ) && !route ) {
    						route = page.route;
    					}
    				}

    				if ( route ) {
    					return route;
    				}
    			}

    			if ( detail.hasOwnProperty( "default" ) ) {
    				return detail.default;
    			}

    			return false;
    		}
    	};
    }

    const pages = createPages();

    // Convenience readable store of all routes.
    const routes = derived( pages, () => {
    	return pages.routes();
    } );

    function parse(str, loose) {
    	if (str instanceof RegExp) return { keys:false, pattern:str };
    	var c, o, tmp, ext, keys=[], pattern='', arr = str.split('/');
    	arr[0] || arr.shift();

    	while (tmp = arr.shift()) {
    		c = tmp[0];
    		if (c === '*') {
    			keys.push('wild');
    			pattern += '/(.*)';
    		} else if (c === ':') {
    			o = tmp.indexOf('?', 1);
    			ext = tmp.indexOf('.', 1);
    			keys.push( tmp.substring(1, !!~o ? o : !!~ext ? ext : tmp.length) );
    			pattern += !!~o && !~ext ? '(?:/([^/]+?))?' : '/([^/]+?)';
    			if (!!~ext) pattern += (!!~o ? '?' : '') + '\\' + tmp.substring(ext);
    		} else {
    			pattern += '/' + tmp;
    		}
    	}

    	return {
    		keys: keys,
    		pattern: new RegExp('^' + pattern + (loose ? '(?=$|\/)' : '\/?$'), 'i')
    	};
    }

    /* node_modules/svelte-spa-router/Router.svelte generated by Svelte v3.49.0 */

    const { Error: Error_1, Object: Object_1$3, console: console_1 } = globals;

    // (267:0) {:else}
    function create_else_block$5(ctx) {
    	let switch_instance;
    	let switch_instance_anchor;
    	let current;
    	const switch_instance_spread_levels = [/*props*/ ctx[2]];
    	var switch_value = /*component*/ ctx[0];

    	function switch_props(ctx) {
    		let switch_instance_props = {};

    		for (let i = 0; i < switch_instance_spread_levels.length; i += 1) {
    			switch_instance_props = assign(switch_instance_props, switch_instance_spread_levels[i]);
    		}

    		return {
    			props: switch_instance_props,
    			$$inline: true
    		};
    	}

    	if (switch_value) {
    		switch_instance = new switch_value(switch_props());
    		switch_instance.$on("routeEvent", /*routeEvent_handler_1*/ ctx[7]);
    	}

    	const block = {
    		c: function create() {
    			if (switch_instance) create_component(switch_instance.$$.fragment);
    			switch_instance_anchor = empty();
    		},
    		m: function mount(target, anchor) {
    			if (switch_instance) {
    				mount_component(switch_instance, target, anchor);
    			}

    			insert_dev(target, switch_instance_anchor, anchor);
    			current = true;
    		},
    		p: function update(ctx, dirty) {
    			const switch_instance_changes = (dirty & /*props*/ 4)
    			? get_spread_update(switch_instance_spread_levels, [get_spread_object(/*props*/ ctx[2])])
    			: {};

    			if (switch_value !== (switch_value = /*component*/ ctx[0])) {
    				if (switch_instance) {
    					group_outros();
    					const old_component = switch_instance;

    					transition_out(old_component.$$.fragment, 1, 0, () => {
    						destroy_component(old_component, 1);
    					});

    					check_outros();
    				}

    				if (switch_value) {
    					switch_instance = new switch_value(switch_props());
    					switch_instance.$on("routeEvent", /*routeEvent_handler_1*/ ctx[7]);
    					create_component(switch_instance.$$.fragment);
    					transition_in(switch_instance.$$.fragment, 1);
    					mount_component(switch_instance, switch_instance_anchor.parentNode, switch_instance_anchor);
    				} else {
    					switch_instance = null;
    				}
    			} else if (switch_value) {
    				switch_instance.$set(switch_instance_changes);
    			}
    		},
    		i: function intro(local) {
    			if (current) return;
    			if (switch_instance) transition_in(switch_instance.$$.fragment, local);
    			current = true;
    		},
    		o: function outro(local) {
    			if (switch_instance) transition_out(switch_instance.$$.fragment, local);
    			current = false;
    		},
    		d: function destroy(detaching) {
    			if (detaching) detach_dev(switch_instance_anchor);
    			if (switch_instance) destroy_component(switch_instance, detaching);
    		}
    	};

    	dispatch_dev("SvelteRegisterBlock", {
    		block,
    		id: create_else_block$5.name,
    		type: "else",
    		source: "(267:0) {:else}",
    		ctx
    	});

    	return block;
    }

    // (260:0) {#if componentParams}
    function create_if_block$q(ctx) {
    	let switch_instance;
    	let switch_instance_anchor;
    	let current;
    	const switch_instance_spread_levels = [{ params: /*componentParams*/ ctx[1] }, /*props*/ ctx[2]];
    	var switch_value = /*component*/ ctx[0];

    	function switch_props(ctx) {
    		let switch_instance_props = {};

    		for (let i = 0; i < switch_instance_spread_levels.length; i += 1) {
    			switch_instance_props = assign(switch_instance_props, switch_instance_spread_levels[i]);
    		}

    		return {
    			props: switch_instance_props,
    			$$inline: true
    		};
    	}

    	if (switch_value) {
    		switch_instance = new switch_value(switch_props());
    		switch_instance.$on("routeEvent", /*routeEvent_handler*/ ctx[6]);
    	}

    	const block = {
    		c: function create() {
    			if (switch_instance) create_component(switch_instance.$$.fragment);
    			switch_instance_anchor = empty();
    		},
    		m: function mount(target, anchor) {
    			if (switch_instance) {
    				mount_component(switch_instance, target, anchor);
    			}

    			insert_dev(target, switch_instance_anchor, anchor);
    			current = true;
    		},
    		p: function update(ctx, dirty) {
    			const switch_instance_changes = (dirty & /*componentParams, props*/ 6)
    			? get_spread_update(switch_instance_spread_levels, [
    					dirty & /*componentParams*/ 2 && { params: /*componentParams*/ ctx[1] },
    					dirty & /*props*/ 4 && get_spread_object(/*props*/ ctx[2])
    				])
    			: {};

    			if (switch_value !== (switch_value = /*component*/ ctx[0])) {
    				if (switch_instance) {
    					group_outros();
    					const old_component = switch_instance;

    					transition_out(old_component.$$.fragment, 1, 0, () => {
    						destroy_component(old_component, 1);
    					});

    					check_outros();
    				}

    				if (switch_value) {
    					switch_instance = new switch_value(switch_props());
    					switch_instance.$on("routeEvent", /*routeEvent_handler*/ ctx[6]);
    					create_component(switch_instance.$$.fragment);
    					transition_in(switch_instance.$$.fragment, 1);
    					mount_component(switch_instance, switch_instance_anchor.parentNode, switch_instance_anchor);
    				} else {
    					switch_instance = null;
    				}
    			} else if (switch_value) {
    				switch_instance.$set(switch_instance_changes);
    			}
    		},
    		i: function intro(local) {
    			if (current) return;
    			if (switch_instance) transition_in(switch_instance.$$.fragment, local);
    			current = true;
    		},
    		o: function outro(local) {
    			if (switch_instance) transition_out(switch_instance.$$.fragment, local);
    			current = false;
    		},
    		d: function destroy(detaching) {
    			if (detaching) detach_dev(switch_instance_anchor);
    			if (switch_instance) destroy_component(switch_instance, detaching);
    		}
    	};

    	dispatch_dev("SvelteRegisterBlock", {
    		block,
    		id: create_if_block$q.name,
    		type: "if",
    		source: "(260:0) {#if componentParams}",
    		ctx
    	});

    	return block;
    }

    function create_fragment$V(ctx) {
    	let current_block_type_index;
    	let if_block;
    	let if_block_anchor;
    	let current;
    	const if_block_creators = [create_if_block$q, create_else_block$5];
    	const if_blocks = [];

    	function select_block_type(ctx, dirty) {
    		if (/*componentParams*/ ctx[1]) return 0;
    		return 1;
    	}

    	current_block_type_index = select_block_type(ctx);
    	if_block = if_blocks[current_block_type_index] = if_block_creators[current_block_type_index](ctx);

    	const block = {
    		c: function create() {
    			if_block.c();
    			if_block_anchor = empty();
    		},
    		l: function claim(nodes) {
    			throw new Error_1("options.hydrate only works if the component was compiled with the `hydratable: true` option");
    		},
    		m: function mount(target, anchor) {
    			if_blocks[current_block_type_index].m(target, anchor);
    			insert_dev(target, if_block_anchor, anchor);
    			current = true;
    		},
    		p: function update(ctx, [dirty]) {
    			let previous_block_index = current_block_type_index;
    			current_block_type_index = select_block_type(ctx);

    			if (current_block_type_index === previous_block_index) {
    				if_blocks[current_block_type_index].p(ctx, dirty);
    			} else {
    				group_outros();

    				transition_out(if_blocks[previous_block_index], 1, 1, () => {
    					if_blocks[previous_block_index] = null;
    				});

    				check_outros();
    				if_block = if_blocks[current_block_type_index];

    				if (!if_block) {
    					if_block = if_blocks[current_block_type_index] = if_block_creators[current_block_type_index](ctx);
    					if_block.c();
    				} else {
    					if_block.p(ctx, dirty);
    				}

    				transition_in(if_block, 1);
    				if_block.m(if_block_anchor.parentNode, if_block_anchor);
    			}
    		},
    		i: function intro(local) {
    			if (current) return;
    			transition_in(if_block);
    			current = true;
    		},
    		o: function outro(local) {
    			transition_out(if_block);
    			current = false;
    		},
    		d: function destroy(detaching) {
    			if_blocks[current_block_type_index].d(detaching);
    			if (detaching) detach_dev(if_block_anchor);
    		}
    	};

    	dispatch_dev("SvelteRegisterBlock", {
    		block,
    		id: create_fragment$V.name,
    		type: "component",
    		source: "",
    		ctx
    	});

    	return block;
    }

    function wrap(component, userData, ...conditions) {
    	// Use the new wrap method and show a deprecation warning
    	// eslint-disable-next-line no-console
    	console.warn('Method `wrap` from `svelte-spa-router` is deprecated and will be removed in a future version. Please use `svelte-spa-router/wrap` instead. See http://bit.ly/svelte-spa-router-upgrading');

    	return wrap$1({ component, userData, conditions });
    }

    /**
     * @typedef {Object} Location
     * @property {string} location - Location (page/view), for example `/book`
     * @property {string} [querystring] - Querystring from the hash, as a string not parsed
     */
    /**
     * Returns the current location from the hash.
     *
     * @returns {Location} Location object
     * @private
     */
    function getLocation() {
    	const hashPosition = window.location.href.indexOf('#/');

    	let location = hashPosition > -1
    	? window.location.href.substr(hashPosition + 1)
    	: '/';

    	// Check if there's a querystring
    	const qsPosition = location.indexOf('?');

    	let querystring = '';

    	if (qsPosition > -1) {
    		querystring = location.substr(qsPosition + 1);
    		location = location.substr(0, qsPosition);
    	}

    	return { location, querystring };
    }

    const loc = readable(null, // eslint-disable-next-line prefer-arrow-callback
    function start(set) {
    	set(getLocation());

    	const update = () => {
    		set(getLocation());
    	};

    	window.addEventListener('hashchange', update, false);

    	return function stop() {
    		window.removeEventListener('hashchange', update, false);
    	};
    });

    const location$1 = derived(loc, $loc => $loc.location);
    const querystring = derived(loc, $loc => $loc.querystring);
    const params = writable(undefined);

    async function push(location) {
    	if (!location || location.length < 1 || location.charAt(0) != '/' && location.indexOf('#/') !== 0) {
    		throw Error('Invalid parameter location');
    	}

    	// Execute this code when the current call stack is complete
    	await tick();

    	// Note: this will include scroll state in history even when restoreScrollState is false
    	history.replaceState(
    		{
    			...history.state,
    			__svelte_spa_router_scrollX: window.scrollX,
    			__svelte_spa_router_scrollY: window.scrollY
    		},
    		undefined
    	);

    	window.location.hash = (location.charAt(0) == '#' ? '' : '#') + location;
    }

    async function pop() {
    	// Execute this code when the current call stack is complete
    	await tick();

    	window.history.back();
    }

    async function replace(location) {
    	if (!location || location.length < 1 || location.charAt(0) != '/' && location.indexOf('#/') !== 0) {
    		throw Error('Invalid parameter location');
    	}

    	// Execute this code when the current call stack is complete
    	await tick();

    	const dest = (location.charAt(0) == '#' ? '' : '#') + location;

    	try {
    		const newState = { ...history.state };
    		delete newState['__svelte_spa_router_scrollX'];
    		delete newState['__svelte_spa_router_scrollY'];
    		window.history.replaceState(newState, undefined, dest);
    	} catch(e) {
    		// eslint-disable-next-line no-console
    		console.warn('Caught exception while replacing the current page. If you\'re running this in the Svelte REPL, please note that the `replace` method might not work in this environment.');
    	}

    	// The method above doesn't trigger the hashchange event, so let's do that manually
    	window.dispatchEvent(new Event('hashchange'));
    }

    function link(node, opts) {
    	opts = linkOpts(opts);

    	// Only apply to <a> tags
    	if (!node || !node.tagName || node.tagName.toLowerCase() != 'a') {
    		throw Error('Action "link" can only be used with <a> tags');
    	}

    	updateLink(node, opts);

    	return {
    		update(updated) {
    			updated = linkOpts(updated);
    			updateLink(node, updated);
    		}
    	};
    }

    function restoreScroll(state) {
    	// If this exists, then this is a back navigation: restore the scroll position
    	if (state) {
    		window.scrollTo(state.__svelte_spa_router_scrollX, state.__svelte_spa_router_scrollY);
    	} else {
    		// Otherwise this is a forward navigation: scroll to top
    		window.scrollTo(0, 0);
    	}
    }

    // Internal function used by the link function
    function updateLink(node, opts) {
    	let href = opts.href || node.getAttribute('href');

    	// Destination must start with '/' or '#/'
    	if (href && href.charAt(0) == '/') {
    		// Add # to the href attribute
    		href = '#' + href;
    	} else if (!href || href.length < 2 || href.slice(0, 2) != '#/') {
    		throw Error('Invalid value for "href" attribute: ' + href);
    	}

    	node.setAttribute('href', href);

    	node.addEventListener('click', event => {
    		// Prevent default anchor onclick behaviour
    		event.preventDefault();

    		if (!opts.disabled) {
    			scrollstateHistoryHandler(event.currentTarget.getAttribute('href'));
    		}
    	});
    }

    // Internal function that ensures the argument of the link action is always an object
    function linkOpts(val) {
    	if (val && typeof val == 'string') {
    		return { href: val };
    	} else {
    		return val || {};
    	}
    }

    /**
     * The handler attached to an anchor tag responsible for updating the
     * current history state with the current scroll state
     *
     * @param {string} href - Destination
     */
    function scrollstateHistoryHandler(href) {
    	// Setting the url (3rd arg) to href will break clicking for reasons, so don't try to do that
    	history.replaceState(
    		{
    			...history.state,
    			__svelte_spa_router_scrollX: window.scrollX,
    			__svelte_spa_router_scrollY: window.scrollY
    		},
    		undefined
    	);

    	// This will force an update as desired, but this time our scroll state will be attached
    	window.location.hash = href;
    }

    function instance$V($$self, $$props, $$invalidate) {
    	let { $$slots: slots = {}, $$scope } = $$props;
    	validate_slots('Router', slots, []);
    	let { routes = {} } = $$props;
    	let { prefix = '' } = $$props;
    	let { restoreScrollState = false } = $$props;

    	/**
     * Container for a route: path, component
     */
    	class RouteItem {
    		/**
     * Initializes the object and creates a regular expression from the path, using regexparam.
     *
     * @param {string} path - Path to the route (must start with '/' or '*')
     * @param {SvelteComponent|WrappedComponent} component - Svelte component for the route, optionally wrapped
     */
    		constructor(path, component) {
    			if (!component || typeof component != 'function' && (typeof component != 'object' || component._sveltesparouter !== true)) {
    				throw Error('Invalid component object');
    			}

    			// Path must be a regular or expression, or a string starting with '/' or '*'
    			if (!path || typeof path == 'string' && (path.length < 1 || path.charAt(0) != '/' && path.charAt(0) != '*') || typeof path == 'object' && !(path instanceof RegExp)) {
    				throw Error('Invalid value for "path" argument - strings must start with / or *');
    			}

    			const { pattern, keys } = parse(path);
    			this.path = path;

    			// Check if the component is wrapped and we have conditions
    			if (typeof component == 'object' && component._sveltesparouter === true) {
    				this.component = component.component;
    				this.conditions = component.conditions || [];
    				this.userData = component.userData;
    				this.props = component.props || {};
    			} else {
    				// Convert the component to a function that returns a Promise, to normalize it
    				this.component = () => Promise.resolve(component);

    				this.conditions = [];
    				this.props = {};
    			}

    			this._pattern = pattern;
    			this._keys = keys;
    		}

    		/**
     * Checks if `path` matches the current route.
     * If there's a match, will return the list of parameters from the URL (if any).
     * In case of no match, the method will return `null`.
     *
     * @param {string} path - Path to test
     * @returns {null|Object.<string, string>} List of paramters from the URL if there's a match, or `null` otherwise.
     */
    		match(path) {
    			// If there's a prefix, check if it matches the start of the path.
    			// If not, bail early, else remove it before we run the matching.
    			if (prefix) {
    				if (typeof prefix == 'string') {
    					if (path.startsWith(prefix)) {
    						path = path.substr(prefix.length) || '/';
    					} else {
    						return null;
    					}
    				} else if (prefix instanceof RegExp) {
    					const match = path.match(prefix);

    					if (match && match[0]) {
    						path = path.substr(match[0].length) || '/';
    					} else {
    						return null;
    					}
    				}
    			}

    			// Check if the pattern matches
    			const matches = this._pattern.exec(path);

    			if (matches === null) {
    				return null;
    			}

    			// If the input was a regular expression, this._keys would be false, so return matches as is
    			if (this._keys === false) {
    				return matches;
    			}

    			const out = {};
    			let i = 0;

    			while (i < this._keys.length) {
    				// In the match parameters, URL-decode all values
    				try {
    					out[this._keys[i]] = decodeURIComponent(matches[i + 1] || '') || null;
    				} catch(e) {
    					out[this._keys[i]] = null;
    				}

    				i++;
    			}

    			return out;
    		}

    		/**
     * Dictionary with route details passed to the pre-conditions functions, as well as the `routeLoading`, `routeLoaded` and `conditionsFailed` events
     * @typedef {Object} RouteDetail
     * @property {string|RegExp} route - Route matched as defined in the route definition (could be a string or a reguar expression object)
     * @property {string} location - Location path
     * @property {string} querystring - Querystring from the hash
     * @property {object} [userData] - Custom data passed by the user
     * @property {SvelteComponent} [component] - Svelte component (only in `routeLoaded` events)
     * @property {string} [name] - Name of the Svelte component (only in `routeLoaded` events)
     */
    		/**
     * Executes all conditions (if any) to control whether the route can be shown. Conditions are executed in the order they are defined, and if a condition fails, the following ones aren't executed.
     * 
     * @param {RouteDetail} detail - Route detail
     * @returns {boolean} Returns true if all the conditions succeeded
     */
    		async checkConditions(detail) {
    			for (let i = 0; i < this.conditions.length; i++) {
    				if (!await this.conditions[i](detail)) {
    					return false;
    				}
    			}

    			return true;
    		}
    	}

    	// Set up all routes
    	const routesList = [];

    	if (routes instanceof Map) {
    		// If it's a map, iterate on it right away
    		routes.forEach((route, path) => {
    			routesList.push(new RouteItem(path, route));
    		});
    	} else {
    		// We have an object, so iterate on its own properties
    		Object.keys(routes).forEach(path => {
    			routesList.push(new RouteItem(path, routes[path]));
    		});
    	}

    	// Props for the component to render
    	let component = null;

    	let componentParams = null;
    	let props = {};

    	// Event dispatcher from Svelte
    	const dispatch = createEventDispatcher();

    	// Just like dispatch, but executes on the next iteration of the event loop
    	async function dispatchNextTick(name, detail) {
    		// Execute this code when the current call stack is complete
    		await tick();

    		dispatch(name, detail);
    	}

    	// If this is set, then that means we have popped into this var the state of our last scroll position
    	let previousScrollState = null;

    	let popStateChanged = null;

    	if (restoreScrollState) {
    		popStateChanged = event => {
    			// If this event was from our history.replaceState, event.state will contain
    			// our scroll history. Otherwise, event.state will be null (like on forward
    			// navigation)
    			if (event.state && (event.state.__svelte_spa_router_scrollY || event.state.__svelte_spa_router_scrollX)) {
    				previousScrollState = event.state;
    			} else {
    				previousScrollState = null;
    			}
    		};

    		// This is removed in the destroy() invocation below
    		window.addEventListener('popstate', popStateChanged);

    		afterUpdate(() => {
    			restoreScroll(previousScrollState);
    		});
    	}

    	// Always have the latest value of loc
    	let lastLoc = null;

    	// Current object of the component loaded
    	let componentObj = null;

    	// Handle hash change events
    	// Listen to changes in the $loc store and update the page
    	// Do not use the $: syntax because it gets triggered by too many things
    	const unsubscribeLoc = loc.subscribe(async newLoc => {
    		lastLoc = newLoc;

    		// Find a route matching the location
    		let i = 0;

    		while (i < routesList.length) {
    			const match = routesList[i].match(newLoc.location);

    			if (!match) {
    				i++;
    				continue;
    			}

    			const detail = {
    				route: routesList[i].path,
    				location: newLoc.location,
    				querystring: newLoc.querystring,
    				userData: routesList[i].userData,
    				params: match && typeof match == 'object' && Object.keys(match).length
    				? match
    				: null
    			};

    			// Check if the route can be loaded - if all conditions succeed
    			if (!await routesList[i].checkConditions(detail)) {
    				// Don't display anything
    				$$invalidate(0, component = null);

    				componentObj = null;

    				// Trigger an event to notify the user, then exit
    				dispatchNextTick('conditionsFailed', detail);

    				return;
    			}

    			// Trigger an event to alert that we're loading the route
    			// We need to clone the object on every event invocation so we don't risk the object to be modified in the next tick
    			dispatchNextTick('routeLoading', Object.assign({}, detail));

    			// If there's a component to show while we're loading the route, display it
    			const obj = routesList[i].component;

    			// Do not replace the component if we're loading the same one as before, to avoid the route being unmounted and re-mounted
    			if (componentObj != obj) {
    				if (obj.loading) {
    					$$invalidate(0, component = obj.loading);
    					componentObj = obj;
    					$$invalidate(1, componentParams = obj.loadingParams);
    					$$invalidate(2, props = {});

    					// Trigger the routeLoaded event for the loading component
    					// Create a copy of detail so we don't modify the object for the dynamic route (and the dynamic route doesn't modify our object too)
    					dispatchNextTick('routeLoaded', Object.assign({}, detail, {
    						component,
    						name: component.name,
    						params: componentParams
    					}));
    				} else {
    					$$invalidate(0, component = null);
    					componentObj = null;
    				}

    				// Invoke the Promise
    				const loaded = await obj();

    				// Now that we're here, after the promise resolved, check if we still want this component, as the user might have navigated to another page in the meanwhile
    				if (newLoc != lastLoc) {
    					// Don't update the component, just exit
    					return;
    				}

    				// If there is a "default" property, which is used by async routes, then pick that
    				$$invalidate(0, component = loaded && loaded.default || loaded);

    				componentObj = obj;
    			}

    			// Set componentParams only if we have a match, to avoid a warning similar to `<Component> was created with unknown prop 'params'`
    			// Of course, this assumes that developers always add a "params" prop when they are expecting parameters
    			if (match && typeof match == 'object' && Object.keys(match).length) {
    				$$invalidate(1, componentParams = match);
    			} else {
    				$$invalidate(1, componentParams = null);
    			}

    			// Set static props, if any
    			$$invalidate(2, props = routesList[i].props);

    			// Dispatch the routeLoaded event then exit
    			// We need to clone the object on every event invocation so we don't risk the object to be modified in the next tick
    			dispatchNextTick('routeLoaded', Object.assign({}, detail, {
    				component,
    				name: component.name,
    				params: componentParams
    			})).then(() => {
    				params.set(componentParams);
    			});

    			return;
    		}

    		// If we're still here, there was no match, so show the empty component
    		$$invalidate(0, component = null);

    		componentObj = null;
    		params.set(undefined);
    	});

    	onDestroy(() => {
    		unsubscribeLoc();
    		popStateChanged && window.removeEventListener('popstate', popStateChanged);
    	});

    	const writable_props = ['routes', 'prefix', 'restoreScrollState'];

    	Object_1$3.keys($$props).forEach(key => {
    		if (!~writable_props.indexOf(key) && key.slice(0, 2) !== '$$' && key !== 'slot') console_1.warn(`<Router> was created with unknown prop '${key}'`);
    	});

    	function routeEvent_handler(event) {
    		bubble.call(this, $$self, event);
    	}

    	function routeEvent_handler_1(event) {
    		bubble.call(this, $$self, event);
    	}

    	$$self.$$set = $$props => {
    		if ('routes' in $$props) $$invalidate(3, routes = $$props.routes);
    		if ('prefix' in $$props) $$invalidate(4, prefix = $$props.prefix);
    		if ('restoreScrollState' in $$props) $$invalidate(5, restoreScrollState = $$props.restoreScrollState);
    	};

    	$$self.$capture_state = () => ({
    		readable,
    		writable,
    		derived,
    		tick,
    		_wrap: wrap$1,
    		wrap,
    		getLocation,
    		loc,
    		location: location$1,
    		querystring,
    		params,
    		push,
    		pop,
    		replace,
    		link,
    		restoreScroll,
    		updateLink,
    		linkOpts,
    		scrollstateHistoryHandler,
    		onDestroy,
    		createEventDispatcher,
    		afterUpdate,
    		parse,
    		routes,
    		prefix,
    		restoreScrollState,
    		RouteItem,
    		routesList,
    		component,
    		componentParams,
    		props,
    		dispatch,
    		dispatchNextTick,
    		previousScrollState,
    		popStateChanged,
    		lastLoc,
    		componentObj,
    		unsubscribeLoc
    	});

    	$$self.$inject_state = $$props => {
    		if ('routes' in $$props) $$invalidate(3, routes = $$props.routes);
    		if ('prefix' in $$props) $$invalidate(4, prefix = $$props.prefix);
    		if ('restoreScrollState' in $$props) $$invalidate(5, restoreScrollState = $$props.restoreScrollState);
    		if ('component' in $$props) $$invalidate(0, component = $$props.component);
    		if ('componentParams' in $$props) $$invalidate(1, componentParams = $$props.componentParams);
    		if ('props' in $$props) $$invalidate(2, props = $$props.props);
    		if ('previousScrollState' in $$props) previousScrollState = $$props.previousScrollState;
    		if ('popStateChanged' in $$props) popStateChanged = $$props.popStateChanged;
    		if ('lastLoc' in $$props) lastLoc = $$props.lastLoc;
    		if ('componentObj' in $$props) componentObj = $$props.componentObj;
    	};

    	if ($$props && "$$inject" in $$props) {
    		$$self.$inject_state($$props.$$inject);
    	}

    	$$self.$$.update = () => {
    		if ($$self.$$.dirty & /*restoreScrollState*/ 32) {
    			// Update history.scrollRestoration depending on restoreScrollState
    			history.scrollRestoration = restoreScrollState ? 'manual' : 'auto';
    		}
    	};

    	return [
    		component,
    		componentParams,
    		props,
    		routes,
    		prefix,
    		restoreScrollState,
    		routeEvent_handler,
    		routeEvent_handler_1
    	];
    }

    class Router extends SvelteComponentDev {
    	constructor(options) {
    		super(options);

    		init(this, options, instance$V, create_fragment$V, safe_not_equal, {
    			routes: 3,
    			prefix: 4,
    			restoreScrollState: 5
    		});

    		dispatch_dev("SvelteRegisterComponent", {
    			component: this,
    			tagName: "Router",
    			options,
    			id: create_fragment$V.name
    		});
    	}

    	get routes() {
    		throw new Error_1("<Router>: Props cannot be read directly from the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
    	}

    	set routes(value) {
    		throw new Error_1("<Router>: Props cannot be set directly on the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
    	}

    	get prefix() {
    		throw new Error_1("<Router>: Props cannot be read directly from the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
    	}

    	set prefix(value) {
    		throw new Error_1("<Router>: Props cannot be set directly on the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
    	}

    	get restoreScrollState() {
    		throw new Error_1("<Router>: Props cannot be read directly from the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
    	}

    	set restoreScrollState(value) {
    		throw new Error_1("<Router>: Props cannot be set directly on the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
    	}
    }

    /* src/amazon-s3-and-cloudfront/ui/components/Page.svelte generated by Svelte v3.49.0 */
    const file$N = "src/amazon-s3-and-cloudfront/ui/components/Page.svelte";

    function create_fragment$U(ctx) {
    	let div;
    	let div_class_value;
    	let current;
    	const default_slot_template = /*#slots*/ ctx[4].default;
    	const default_slot = create_slot(default_slot_template, ctx, /*$$scope*/ ctx[3], null);

    	const block = {
    		c: function create() {
    			div = element("div");
    			if (default_slot) default_slot.c();
    			attr_dev(div, "class", div_class_value = "page-wrapper " + /*name*/ ctx[0]);
    			toggle_class(div, "subpage", /*subpage*/ ctx[1]);
    			add_location(div, file$N, 30, 0, 796);
    		},
    		l: function claim(nodes) {
    			throw new Error("options.hydrate only works if the component was compiled with the `hydratable: true` option");
    		},
    		m: function mount(target, anchor) {
    			insert_dev(target, div, anchor);

    			if (default_slot) {
    				default_slot.m(div, null);
    			}

    			current = true;
    		},
    		p: function update(ctx, [dirty]) {
    			if (default_slot) {
    				if (default_slot.p && (!current || dirty & /*$$scope*/ 8)) {
    					update_slot_base(
    						default_slot,
    						default_slot_template,
    						ctx,
    						/*$$scope*/ ctx[3],
    						!current
    						? get_all_dirty_from_scope(/*$$scope*/ ctx[3])
    						: get_slot_changes(default_slot_template, /*$$scope*/ ctx[3], dirty, null),
    						null
    					);
    				}
    			}

    			if (!current || dirty & /*name*/ 1 && div_class_value !== (div_class_value = "page-wrapper " + /*name*/ ctx[0])) {
    				attr_dev(div, "class", div_class_value);
    			}

    			if (dirty & /*name, subpage*/ 3) {
    				toggle_class(div, "subpage", /*subpage*/ ctx[1]);
    			}
    		},
    		i: function intro(local) {
    			if (current) return;
    			transition_in(default_slot, local);
    			current = true;
    		},
    		o: function outro(local) {
    			transition_out(default_slot, local);
    			current = false;
    		},
    		d: function destroy(detaching) {
    			if (detaching) detach_dev(div);
    			if (default_slot) default_slot.d(detaching);
    		}
    	};

    	dispatch_dev("SvelteRegisterBlock", {
    		block,
    		id: create_fragment$U.name,
    		type: "component",
    		source: "",
    		ctx
    	});

    	return block;
    }

    function instance$U($$self, $$props, $$invalidate) {
    	let $location;
    	let $current_settings;
    	validate_store(location$1, 'location');
    	component_subscribe($$self, location$1, $$value => $$invalidate(5, $location = $$value));
    	validate_store(current_settings, 'current_settings');
    	component_subscribe($$self, current_settings, $$value => $$invalidate(6, $current_settings = $$value));
    	let { $$slots: slots = {}, $$scope } = $$props;
    	validate_slots('Page', slots, ['default']);
    	let { name = "" } = $$props;
    	let { subpage = false } = $$props;
    	let { initialSettings = $current_settings } = $$props;
    	const dispatch = createEventDispatcher();

    	// When a page is created, store a copy of the initial settings
    	// so they can be compared with any changes later.
    	setContext("initialSettings", initialSettings);

    	// Tell the route event handlers about the initial settings too.
    	onMount(() => {
    		dispatch("routeEvent", {
    			event: "page.initial.settings",
    			data: {
    				settings: initialSettings,
    				location: $location
    			}
    		});
    	});

    	const writable_props = ['name', 'subpage', 'initialSettings'];

    	Object.keys($$props).forEach(key => {
    		if (!~writable_props.indexOf(key) && key.slice(0, 2) !== '$$' && key !== 'slot') console.warn(`<Page> was created with unknown prop '${key}'`);
    	});

    	$$self.$$set = $$props => {
    		if ('name' in $$props) $$invalidate(0, name = $$props.name);
    		if ('subpage' in $$props) $$invalidate(1, subpage = $$props.subpage);
    		if ('initialSettings' in $$props) $$invalidate(2, initialSettings = $$props.initialSettings);
    		if ('$$scope' in $$props) $$invalidate(3, $$scope = $$props.$$scope);
    	};

    	$$self.$capture_state = () => ({
    		onMount,
    		createEventDispatcher,
    		setContext,
    		location: location$1,
    		current_settings,
    		name,
    		subpage,
    		initialSettings,
    		dispatch,
    		$location,
    		$current_settings
    	});

    	$$self.$inject_state = $$props => {
    		if ('name' in $$props) $$invalidate(0, name = $$props.name);
    		if ('subpage' in $$props) $$invalidate(1, subpage = $$props.subpage);
    		if ('initialSettings' in $$props) $$invalidate(2, initialSettings = $$props.initialSettings);
    	};

    	if ($$props && "$$inject" in $$props) {
    		$$self.$inject_state($$props.$$inject);
    	}

    	return [name, subpage, initialSettings, $$scope, slots];
    }

    class Page extends SvelteComponentDev {
    	constructor(options) {
    		super(options);
    		init(this, options, instance$U, create_fragment$U, safe_not_equal, { name: 0, subpage: 1, initialSettings: 2 });

    		dispatch_dev("SvelteRegisterComponent", {
    			component: this,
    			tagName: "Page",
    			options,
    			id: create_fragment$U.name
    		});
    	}

    	get name() {
    		throw new Error("<Page>: Props cannot be read directly from the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
    	}

    	set name(value) {
    		throw new Error("<Page>: Props cannot be set directly on the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
    	}

    	get subpage() {
    		throw new Error("<Page>: Props cannot be read directly from the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
    	}

    	set subpage(value) {
    		throw new Error("<Page>: Props cannot be set directly on the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
    	}

    	get initialSettings() {
    		throw new Error("<Page>: Props cannot be read directly from the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
    	}

    	set initialSettings(value) {
    		throw new Error("<Page>: Props cannot be set directly on the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
    	}
    }

    function cubicOut(t) {
        const f = t - 1.0;
        return f * f * f + 1.0;
    }

    function slide(node, { delay = 0, duration = 400, easing = cubicOut } = {}) {
        const style = getComputedStyle(node);
        const opacity = +style.opacity;
        const height = parseFloat(style.height);
        const padding_top = parseFloat(style.paddingTop);
        const padding_bottom = parseFloat(style.paddingBottom);
        const margin_top = parseFloat(style.marginTop);
        const margin_bottom = parseFloat(style.marginBottom);
        const border_top_width = parseFloat(style.borderTopWidth);
        const border_bottom_width = parseFloat(style.borderBottomWidth);
        return {
            delay,
            duration,
            easing,
            css: t => 'overflow: hidden;' +
                `opacity: ${Math.min(t * 20, 1) * opacity};` +
                `height: ${t * height}px;` +
                `padding-top: ${t * padding_top}px;` +
                `padding-bottom: ${t * padding_bottom}px;` +
                `margin-top: ${t * margin_top}px;` +
                `margin-bottom: ${t * margin_bottom}px;` +
                `border-top-width: ${t * border_top_width}px;` +
                `border-bottom-width: ${t * border_bottom_width}px;`
        };
    }
    function scale(node, { delay = 0, duration = 400, easing = cubicOut, start = 0, opacity = 0 } = {}) {
        const style = getComputedStyle(node);
        const target_opacity = +style.opacity;
        const transform = style.transform === 'none' ? '' : style.transform;
        const sd = 1 - start;
        const od = target_opacity * (1 - opacity);
        return {
            delay,
            duration,
            easing,
            css: (_t, u) => `
			transform: ${transform} scale(${1 - (sd * u)});
			opacity: ${target_opacity - (od * u)}
		`
        };
    }

    /* src/amazon-s3-and-cloudfront/ui/components/Notification.svelte generated by Svelte v3.49.0 */
    const file$M = "src/amazon-s3-and-cloudfront/ui/components/Notification.svelte";
    const get_details_slot_changes = dirty => ({});
    const get_details_slot_context = ctx => ({});

    // (95:2) {#if iconURL}
    function create_if_block_8$3(ctx) {
    	let div;
    	let img;
    	let img_src_value;
    	let img_alt_value;
    	let div_resize_listener;

    	const block = {
    		c: function create() {
    			div = element("div");
    			img = element("img");
    			attr_dev(img, "class", "icon type");
    			if (!src_url_equal(img.src, img_src_value = /*iconURL*/ ctx[18])) attr_dev(img, "src", img_src_value);
    			attr_dev(img, "alt", img_alt_value = "" + (/*notification*/ ctx[0].type + " icon"));
    			add_location(img, file$M, 96, 4, 2620);
    			attr_dev(div, "class", "icon type");
    			add_render_callback(() => /*div_elementresize_handler*/ ctx[24].call(div));
    			add_location(div, file$M, 95, 3, 2561);
    		},
    		m: function mount(target, anchor) {
    			insert_dev(target, div, anchor);
    			append_dev(div, img);
    			div_resize_listener = add_resize_listener(div, /*div_elementresize_handler*/ ctx[24].bind(div));
    		},
    		p: function update(ctx, dirty) {
    			if (dirty & /*iconURL*/ 262144 && !src_url_equal(img.src, img_src_value = /*iconURL*/ ctx[18])) {
    				attr_dev(img, "src", img_src_value);
    			}

    			if (dirty & /*notification*/ 1 && img_alt_value !== (img_alt_value = "" + (/*notification*/ ctx[0].type + " icon"))) {
    				attr_dev(img, "alt", img_alt_value);
    			}
    		},
    		d: function destroy(detaching) {
    			if (detaching) detach_dev(div);
    			div_resize_listener();
    		}
    	};

    	dispatch_dev("SvelteRegisterBlock", {
    		block,
    		id: create_if_block_8$3.name,
    		type: "if",
    		source: "(95:2) {#if iconURL}",
    		ctx
    	});

    	return block;
    }

    // (101:3) {#if heading || dismissible || expandable}
    function create_if_block_2$8(ctx) {
    	let div;
    	let t;
    	let if_block0 = /*heading*/ ctx[8] && create_if_block_6$4(ctx);

    	function select_block_type_1(ctx, dirty) {
    		if (/*dismissible*/ ctx[9] && /*expandable*/ ctx[12]) return create_if_block_3$5;
    		if (/*expandable*/ ctx[12]) return create_if_block_4$5;
    		if (/*dismissible*/ ctx[9]) return create_if_block_5$4;
    	}

    	let current_block_type = select_block_type_1(ctx);
    	let if_block1 = current_block_type && current_block_type(ctx);

    	const block = {
    		c: function create() {
    			div = element("div");
    			if (if_block0) if_block0.c();
    			t = space();
    			if (if_block1) if_block1.c();
    			attr_dev(div, "class", "heading");
    			add_location(div, file$M, 101, 4, 2810);
    		},
    		m: function mount(target, anchor) {
    			insert_dev(target, div, anchor);
    			if (if_block0) if_block0.m(div, null);
    			append_dev(div, t);
    			if (if_block1) if_block1.m(div, null);
    		},
    		p: function update(ctx, dirty) {
    			if (/*heading*/ ctx[8]) {
    				if (if_block0) {
    					if_block0.p(ctx, dirty);
    				} else {
    					if_block0 = create_if_block_6$4(ctx);
    					if_block0.c();
    					if_block0.m(div, t);
    				}
    			} else if (if_block0) {
    				if_block0.d(1);
    				if_block0 = null;
    			}

    			if (current_block_type === (current_block_type = select_block_type_1(ctx)) && if_block1) {
    				if_block1.p(ctx, dirty);
    			} else {
    				if (if_block1) if_block1.d(1);
    				if_block1 = current_block_type && current_block_type(ctx);

    				if (if_block1) {
    					if_block1.c();
    					if_block1.m(div, null);
    				}
    			}
    		},
    		d: function destroy(detaching) {
    			if (detaching) detach_dev(div);
    			if (if_block0) if_block0.d();

    			if (if_block1) {
    				if_block1.d();
    			}
    		}
    	};

    	dispatch_dev("SvelteRegisterBlock", {
    		block,
    		id: create_if_block_2$8.name,
    		type: "if",
    		source: "(101:3) {#if heading || dismissible || expandable}",
    		ctx
    	});

    	return block;
    }

    // (103:5) {#if heading}
    function create_if_block_6$4(ctx) {
    	let if_block_anchor;

    	function select_block_type(ctx, dirty) {
    		if (/*plainHeading*/ ctx[10]) return create_if_block_7$4;
    		return create_else_block$4;
    	}

    	let current_block_type = select_block_type(ctx);
    	let if_block = current_block_type(ctx);

    	const block = {
    		c: function create() {
    			if_block.c();
    			if_block_anchor = empty();
    		},
    		m: function mount(target, anchor) {
    			if_block.m(target, anchor);
    			insert_dev(target, if_block_anchor, anchor);
    		},
    		p: function update(ctx, dirty) {
    			if (current_block_type === (current_block_type = select_block_type(ctx)) && if_block) {
    				if_block.p(ctx, dirty);
    			} else {
    				if_block.d(1);
    				if_block = current_block_type(ctx);

    				if (if_block) {
    					if_block.c();
    					if_block.m(if_block_anchor.parentNode, if_block_anchor);
    				}
    			}
    		},
    		d: function destroy(detaching) {
    			if_block.d(detaching);
    			if (detaching) detach_dev(if_block_anchor);
    		}
    	};

    	dispatch_dev("SvelteRegisterBlock", {
    		block,
    		id: create_if_block_6$4.name,
    		type: "if",
    		source: "(103:5) {#if heading}",
    		ctx
    	});

    	return block;
    }

    // (106:6) {:else}
    function create_else_block$4(ctx) {
    	let h3;

    	const block = {
    		c: function create() {
    			h3 = element("h3");
    			add_location(h3, file$M, 106, 7, 2927);
    		},
    		m: function mount(target, anchor) {
    			insert_dev(target, h3, anchor);
    			h3.innerHTML = /*heading*/ ctx[8];
    		},
    		p: function update(ctx, dirty) {
    			if (dirty & /*heading*/ 256) h3.innerHTML = /*heading*/ ctx[8];		},
    		d: function destroy(detaching) {
    			if (detaching) detach_dev(h3);
    		}
    	};

    	dispatch_dev("SvelteRegisterBlock", {
    		block,
    		id: create_else_block$4.name,
    		type: "else",
    		source: "(106:6) {:else}",
    		ctx
    	});

    	return block;
    }

    // (104:6) {#if plainHeading}
    function create_if_block_7$4(ctx) {
    	let p;

    	const block = {
    		c: function create() {
    			p = element("p");
    			add_location(p, file$M, 104, 7, 2883);
    		},
    		m: function mount(target, anchor) {
    			insert_dev(target, p, anchor);
    			p.innerHTML = /*heading*/ ctx[8];
    		},
    		p: function update(ctx, dirty) {
    			if (dirty & /*heading*/ 256) p.innerHTML = /*heading*/ ctx[8];		},
    		d: function destroy(detaching) {
    			if (detaching) detach_dev(p);
    		}
    	};

    	dispatch_dev("SvelteRegisterBlock", {
    		block,
    		id: create_if_block_7$4.name,
    		type: "if",
    		source: "(104:6) {#if plainHeading}",
    		ctx
    	});

    	return block;
    }

    // (115:27) 
    function create_if_block_5$4(ctx) {
    	let button;
    	let button_title_value;
    	let mounted;
    	let dispose;

    	const block = {
    		c: function create() {
    			button = element("button");
    			attr_dev(button, "class", "icon close");
    			attr_dev(button, "title", button_title_value = /*$strings*/ ctx[19]["dismiss_notice"]);
    			add_location(button, file$M, 115, 6, 3546);
    		},
    		m: function mount(target, anchor) {
    			insert_dev(target, button, anchor);

    			if (!mounted) {
    				dispose = listen_dev(button, "click", prevent_default(/*click_handler_2*/ ctx[27]), false, true, false);
    				mounted = true;
    			}
    		},
    		p: function update(ctx, dirty) {
    			if (dirty & /*$strings*/ 524288 && button_title_value !== (button_title_value = /*$strings*/ ctx[19]["dismiss_notice"])) {
    				attr_dev(button, "title", button_title_value);
    			}
    		},
    		d: function destroy(detaching) {
    			if (detaching) detach_dev(button);
    			mounted = false;
    			dispose();
    		}
    	};

    	dispatch_dev("SvelteRegisterBlock", {
    		block,
    		id: create_if_block_5$4.name,
    		type: "if",
    		source: "(115:27) ",
    		ctx
    	});

    	return block;
    }

    // (113:26) 
    function create_if_block_4$5(ctx) {
    	let button;
    	let button_title_value;
    	let mounted;
    	let dispose;

    	const block = {
    		c: function create() {
    			button = element("button");
    			attr_dev(button, "class", "expandable");

    			attr_dev(button, "title", button_title_value = /*expanded*/ ctx[1]
    			? /*$strings*/ ctx[19].hide_details
    			: /*$strings*/ ctx[19].show_details);

    			toggle_class(button, "expanded", /*expanded*/ ctx[1]);
    			add_location(button, file$M, 113, 6, 3342);
    		},
    		m: function mount(target, anchor) {
    			insert_dev(target, button, anchor);

    			if (!mounted) {
    				dispose = listen_dev(button, "click", prevent_default(/*click_handler_1*/ ctx[26]), false, true, false);
    				mounted = true;
    			}
    		},
    		p: function update(ctx, dirty) {
    			if (dirty & /*expanded, $strings*/ 524290 && button_title_value !== (button_title_value = /*expanded*/ ctx[1]
    			? /*$strings*/ ctx[19].hide_details
    			: /*$strings*/ ctx[19].show_details)) {
    				attr_dev(button, "title", button_title_value);
    			}

    			if (dirty & /*expanded*/ 2) {
    				toggle_class(button, "expanded", /*expanded*/ ctx[1]);
    			}
    		},
    		d: function destroy(detaching) {
    			if (detaching) detach_dev(button);
    			mounted = false;
    			dispose();
    		}
    	};

    	dispatch_dev("SvelteRegisterBlock", {
    		block,
    		id: create_if_block_4$5.name,
    		type: "if",
    		source: "(113:26) ",
    		ctx
    	});

    	return block;
    }

    // (110:5) {#if dismissible && expandable}
    function create_if_block_3$5(ctx) {
    	let button0;
    	let t0_value = /*$strings*/ ctx[19].dismiss_all + "";
    	let t0;
    	let t1;
    	let button1;
    	let button1_title_value;
    	let mounted;
    	let dispose;

    	const block = {
    		c: function create() {
    			button0 = element("button");
    			t0 = text(t0_value);
    			t1 = space();
    			button1 = element("button");
    			attr_dev(button0, "class", "dismiss");
    			add_location(button0, file$M, 110, 6, 3018);
    			attr_dev(button1, "class", "expandable");

    			attr_dev(button1, "title", button1_title_value = /*expanded*/ ctx[1]
    			? /*$strings*/ ctx[19].hide_details
    			: /*$strings*/ ctx[19].show_details);

    			toggle_class(button1, "expanded", /*expanded*/ ctx[1]);
    			add_location(button1, file$M, 111, 6, 3139);
    		},
    		m: function mount(target, anchor) {
    			insert_dev(target, button0, anchor);
    			append_dev(button0, t0);
    			insert_dev(target, t1, anchor);
    			insert_dev(target, button1, anchor);

    			if (!mounted) {
    				dispose = [
    					listen_dev(
    						button0,
    						"click",
    						prevent_default(function () {
    							if (is_function(notifications.dismiss(/*unique_id*/ ctx[2]))) notifications.dismiss(/*unique_id*/ ctx[2]).apply(this, arguments);
    						}),
    						false,
    						true,
    						false
    					),
    					listen_dev(button1, "click", prevent_default(/*click_handler*/ ctx[25]), false, true, false)
    				];

    				mounted = true;
    			}
    		},
    		p: function update(new_ctx, dirty) {
    			ctx = new_ctx;
    			if (dirty & /*$strings*/ 524288 && t0_value !== (t0_value = /*$strings*/ ctx[19].dismiss_all + "")) set_data_dev(t0, t0_value);

    			if (dirty & /*expanded, $strings*/ 524290 && button1_title_value !== (button1_title_value = /*expanded*/ ctx[1]
    			? /*$strings*/ ctx[19].hide_details
    			: /*$strings*/ ctx[19].show_details)) {
    				attr_dev(button1, "title", button1_title_value);
    			}

    			if (dirty & /*expanded*/ 2) {
    				toggle_class(button1, "expanded", /*expanded*/ ctx[1]);
    			}
    		},
    		d: function destroy(detaching) {
    			if (detaching) detach_dev(button0);
    			if (detaching) detach_dev(t1);
    			if (detaching) detach_dev(button1);
    			mounted = false;
    			run_all(dispose);
    		}
    	};

    	dispatch_dev("SvelteRegisterBlock", {
    		block,
    		id: create_if_block_3$5.name,
    		type: "if",
    		source: "(110:5) {#if dismissible && expandable}",
    		ctx
    	});

    	return block;
    }

    // (121:3) {#if extra}
    function create_if_block_1$c(ctx) {
    	let p;

    	const block = {
    		c: function create() {
    			p = element("p");
    			add_location(p, file$M, 121, 4, 3744);
    		},
    		m: function mount(target, anchor) {
    			insert_dev(target, p, anchor);
    			p.innerHTML = /*extra*/ ctx[11];
    		},
    		p: function update(ctx, dirty) {
    			if (dirty & /*extra*/ 2048) p.innerHTML = /*extra*/ ctx[11];		},
    		d: function destroy(detaching) {
    			if (detaching) detach_dev(p);
    		}
    	};

    	dispatch_dev("SvelteRegisterBlock", {
    		block,
    		id: create_if_block_1$c.name,
    		type: "if",
    		source: "(121:3) {#if extra}",
    		ctx
    	});

    	return block;
    }

    // (124:3) {#if linksHTML}
    function create_if_block$p(ctx) {
    	let p;

    	const block = {
    		c: function create() {
    			p = element("p");
    			attr_dev(p, "class", "links");
    			add_location(p, file$M, 124, 4, 3797);
    		},
    		m: function mount(target, anchor) {
    			insert_dev(target, p, anchor);
    			p.innerHTML = /*linksHTML*/ ctx[16];
    		},
    		p: function update(ctx, dirty) {
    			if (dirty & /*linksHTML*/ 65536) p.innerHTML = /*linksHTML*/ ctx[16];		},
    		d: function destroy(detaching) {
    			if (detaching) detach_dev(p);
    		}
    	};

    	dispatch_dev("SvelteRegisterBlock", {
    		block,
    		id: create_if_block$p.name,
    		type: "if",
    		source: "(124:3) {#if linksHTML}",
    		ctx
    	});

    	return block;
    }

    function create_fragment$T(ctx) {
    	let div2;
    	let div1;
    	let t0;
    	let div0;
    	let t1;
    	let t2;
    	let t3;
    	let div0_resize_listener;
    	let t4;
    	let div2_transition;
    	let current;
    	let if_block0 = /*iconURL*/ ctx[18] && create_if_block_8$3(ctx);
    	let if_block1 = (/*heading*/ ctx[8] || /*dismissible*/ ctx[9] || /*expandable*/ ctx[12]) && create_if_block_2$8(ctx);
    	const default_slot_template = /*#slots*/ ctx[23].default;
    	const default_slot = create_slot(default_slot_template, ctx, /*$$scope*/ ctx[22], null);
    	let if_block2 = /*extra*/ ctx[11] && create_if_block_1$c(ctx);
    	let if_block3 = /*linksHTML*/ ctx[16] && create_if_block$p(ctx);
    	const details_slot_template = /*#slots*/ ctx[23].details;
    	const details_slot = create_slot(details_slot_template, ctx, /*$$scope*/ ctx[22], get_details_slot_context);

    	const block = {
    		c: function create() {
    			div2 = element("div");
    			div1 = element("div");
    			if (if_block0) if_block0.c();
    			t0 = space();
    			div0 = element("div");
    			if (if_block1) if_block1.c();
    			t1 = space();
    			if (default_slot) default_slot.c();
    			t2 = space();
    			if (if_block2) if_block2.c();
    			t3 = space();
    			if (if_block3) if_block3.c();
    			t4 = space();
    			if (details_slot) details_slot.c();
    			attr_dev(div0, "class", "body");
    			add_render_callback(() => /*div0_elementresize_handler*/ ctx[28].call(div0));
    			add_location(div0, file$M, 99, 2, 2710);
    			attr_dev(div1, "class", "content");
    			add_location(div1, file$M, 93, 1, 2520);
    			attr_dev(div2, "class", "notification");
    			toggle_class(div2, "inline", /*inline*/ ctx[3]);
    			toggle_class(div2, "wordpress", /*wordpress*/ ctx[4]);
    			toggle_class(div2, "success", /*success*/ ctx[5]);
    			toggle_class(div2, "warning", /*warning*/ ctx[6]);
    			toggle_class(div2, "error", /*error*/ ctx[7]);
    			toggle_class(div2, "info", /*info*/ ctx[15]);
    			toggle_class(div2, "multiline", /*multiline*/ ctx[17]);
    			toggle_class(div2, "expandable", /*expandable*/ ctx[12]);
    			toggle_class(div2, "expanded", /*expanded*/ ctx[1]);
    			add_location(div2, file$M, 80, 0, 2329);
    		},
    		l: function claim(nodes) {
    			throw new Error("options.hydrate only works if the component was compiled with the `hydratable: true` option");
    		},
    		m: function mount(target, anchor) {
    			insert_dev(target, div2, anchor);
    			append_dev(div2, div1);
    			if (if_block0) if_block0.m(div1, null);
    			append_dev(div1, t0);
    			append_dev(div1, div0);
    			if (if_block1) if_block1.m(div0, null);
    			append_dev(div0, t1);

    			if (default_slot) {
    				default_slot.m(div0, null);
    			}

    			append_dev(div0, t2);
    			if (if_block2) if_block2.m(div0, null);
    			append_dev(div0, t3);
    			if (if_block3) if_block3.m(div0, null);
    			div0_resize_listener = add_resize_listener(div0, /*div0_elementresize_handler*/ ctx[28].bind(div0));
    			append_dev(div2, t4);

    			if (details_slot) {
    				details_slot.m(div2, null);
    			}

    			current = true;
    		},
    		p: function update(ctx, [dirty]) {
    			if (/*iconURL*/ ctx[18]) {
    				if (if_block0) {
    					if_block0.p(ctx, dirty);
    				} else {
    					if_block0 = create_if_block_8$3(ctx);
    					if_block0.c();
    					if_block0.m(div1, t0);
    				}
    			} else if (if_block0) {
    				if_block0.d(1);
    				if_block0 = null;
    			}

    			if (/*heading*/ ctx[8] || /*dismissible*/ ctx[9] || /*expandable*/ ctx[12]) {
    				if (if_block1) {
    					if_block1.p(ctx, dirty);
    				} else {
    					if_block1 = create_if_block_2$8(ctx);
    					if_block1.c();
    					if_block1.m(div0, t1);
    				}
    			} else if (if_block1) {
    				if_block1.d(1);
    				if_block1 = null;
    			}

    			if (default_slot) {
    				if (default_slot.p && (!current || dirty & /*$$scope*/ 4194304)) {
    					update_slot_base(
    						default_slot,
    						default_slot_template,
    						ctx,
    						/*$$scope*/ ctx[22],
    						!current
    						? get_all_dirty_from_scope(/*$$scope*/ ctx[22])
    						: get_slot_changes(default_slot_template, /*$$scope*/ ctx[22], dirty, null),
    						null
    					);
    				}
    			}

    			if (/*extra*/ ctx[11]) {
    				if (if_block2) {
    					if_block2.p(ctx, dirty);
    				} else {
    					if_block2 = create_if_block_1$c(ctx);
    					if_block2.c();
    					if_block2.m(div0, t3);
    				}
    			} else if (if_block2) {
    				if_block2.d(1);
    				if_block2 = null;
    			}

    			if (/*linksHTML*/ ctx[16]) {
    				if (if_block3) {
    					if_block3.p(ctx, dirty);
    				} else {
    					if_block3 = create_if_block$p(ctx);
    					if_block3.c();
    					if_block3.m(div0, null);
    				}
    			} else if (if_block3) {
    				if_block3.d(1);
    				if_block3 = null;
    			}

    			if (details_slot) {
    				if (details_slot.p && (!current || dirty & /*$$scope*/ 4194304)) {
    					update_slot_base(
    						details_slot,
    						details_slot_template,
    						ctx,
    						/*$$scope*/ ctx[22],
    						!current
    						? get_all_dirty_from_scope(/*$$scope*/ ctx[22])
    						: get_slot_changes(details_slot_template, /*$$scope*/ ctx[22], dirty, get_details_slot_changes),
    						get_details_slot_context
    					);
    				}
    			}

    			if (dirty & /*inline*/ 8) {
    				toggle_class(div2, "inline", /*inline*/ ctx[3]);
    			}

    			if (dirty & /*wordpress*/ 16) {
    				toggle_class(div2, "wordpress", /*wordpress*/ ctx[4]);
    			}

    			if (dirty & /*success*/ 32) {
    				toggle_class(div2, "success", /*success*/ ctx[5]);
    			}

    			if (dirty & /*warning*/ 64) {
    				toggle_class(div2, "warning", /*warning*/ ctx[6]);
    			}

    			if (dirty & /*error*/ 128) {
    				toggle_class(div2, "error", /*error*/ ctx[7]);
    			}

    			if (dirty & /*info*/ 32768) {
    				toggle_class(div2, "info", /*info*/ ctx[15]);
    			}

    			if (dirty & /*multiline*/ 131072) {
    				toggle_class(div2, "multiline", /*multiline*/ ctx[17]);
    			}

    			if (dirty & /*expandable*/ 4096) {
    				toggle_class(div2, "expandable", /*expandable*/ ctx[12]);
    			}

    			if (dirty & /*expanded*/ 2) {
    				toggle_class(div2, "expanded", /*expanded*/ ctx[1]);
    			}
    		},
    		i: function intro(local) {
    			if (current) return;
    			transition_in(default_slot, local);
    			transition_in(details_slot, local);

    			if (local) {
    				add_render_callback(() => {
    					if (!div2_transition) div2_transition = create_bidirectional_transition(div2, slide, {}, true);
    					div2_transition.run(1);
    				});
    			}

    			current = true;
    		},
    		o: function outro(local) {
    			transition_out(default_slot, local);
    			transition_out(details_slot, local);

    			if (local) {
    				if (!div2_transition) div2_transition = create_bidirectional_transition(div2, slide, {}, false);
    				div2_transition.run(0);
    			}

    			current = false;
    		},
    		d: function destroy(detaching) {
    			if (detaching) detach_dev(div2);
    			if (if_block0) if_block0.d();
    			if (if_block1) if_block1.d();
    			if (default_slot) default_slot.d(detaching);
    			if (if_block2) if_block2.d();
    			if (if_block3) if_block3.d();
    			div0_resize_listener();
    			if (details_slot) details_slot.d(detaching);
    			if (detaching && div2_transition) div2_transition.end();
    		}
    	};

    	dispatch_dev("SvelteRegisterBlock", {
    		block,
    		id: create_fragment$T.name,
    		type: "component",
    		source: "",
    		ctx
    	});

    	return block;
    }

    function getLinksHTML(links) {
    	if (links.length) {
    		return links.join(" ");
    	}

    	return "";
    }

    function instance$T($$self, $$props, $$invalidate) {
    	let iconURL;
    	let multiline;
    	let linksHTML;
    	let $urls;
    	let $strings;
    	validate_store(urls, 'urls');
    	component_subscribe($$self, urls, $$value => $$invalidate(29, $urls = $$value));
    	validate_store(strings, 'strings');
    	component_subscribe($$self, strings, $$value => $$invalidate(19, $strings = $$value));
    	let { $$slots: slots = {}, $$scope } = $$props;
    	validate_slots('Notification', slots, ['default','details']);
    	let { notification = {} } = $$props;
    	let { unique_id = notification.id ? notification.id : "" } = $$props;
    	let { inline = notification.inline ? notification.inline : false } = $$props;
    	let { wordpress = notification.wordpress ? notification.wordpress : false } = $$props;
    	let { success = notification.type === "success" } = $$props;
    	let { warning = notification.type === "warning" } = $$props;
    	let { error = notification.type === "error" } = $$props;
    	let info = false;

    	// It's possible to set type purely by component property,
    	// but we need notification.type to be correct too.
    	if (success) {
    		notification.type = "success";
    	} else if (warning) {
    		notification.type = "warning";
    	} else if (error) {
    		notification.type = "error";
    	} else {
    		info = true;
    		notification.type = "info";
    	}

    	let { heading = notification.hasOwnProperty("heading") && notification.heading.trim().length
    	? notification.heading.trim()
    	: "" } = $$props;

    	let { dismissible = notification.dismissible
    	? notification.dismissible
    	: false } = $$props;

    	let { icon = notification.icon ? notification.icon : false } = $$props;

    	let { plainHeading = notification.plainHeading
    	? notification.plainHeading
    	: false } = $$props;

    	let { extra = notification.extra ? notification.extra : "" } = $$props;
    	let { links = notification.links ? notification.links : [] } = $$props;
    	let { expandable = false } = $$props;
    	let { expanded = false } = $$props;

    	/**
     * Returns the icon URL for the notification.
     *
     * @param {string|boolean} icon
     * @param {string} notificationType
     *
     * @return {string}
     */
    	function getIconURL(icon, notificationType) {
    		if (icon) {
    			return $urls.assets + "img/icon/" + icon;
    		}

    		return $urls.assets + "img/icon/notification-" + notificationType + ".svg";
    	}

    	// We need to change various properties and alignments if text is multiline.
    	let iconHeight = 0;

    	let bodyHeight = 0;

    	const writable_props = [
    		'notification',
    		'unique_id',
    		'inline',
    		'wordpress',
    		'success',
    		'warning',
    		'error',
    		'heading',
    		'dismissible',
    		'icon',
    		'plainHeading',
    		'extra',
    		'links',
    		'expandable',
    		'expanded'
    	];

    	Object.keys($$props).forEach(key => {
    		if (!~writable_props.indexOf(key) && key.slice(0, 2) !== '$$' && key !== 'slot') console.warn(`<Notification> was created with unknown prop '${key}'`);
    	});

    	function div_elementresize_handler() {
    		iconHeight = this.clientHeight;
    		$$invalidate(13, iconHeight);
    	}

    	const click_handler = () => $$invalidate(1, expanded = !expanded);
    	const click_handler_1 = () => $$invalidate(1, expanded = !expanded);
    	const click_handler_2 = () => notifications.dismiss(unique_id);

    	function div0_elementresize_handler() {
    		bodyHeight = this.clientHeight;
    		$$invalidate(14, bodyHeight);
    	}

    	$$self.$$set = $$props => {
    		if ('notification' in $$props) $$invalidate(0, notification = $$props.notification);
    		if ('unique_id' in $$props) $$invalidate(2, unique_id = $$props.unique_id);
    		if ('inline' in $$props) $$invalidate(3, inline = $$props.inline);
    		if ('wordpress' in $$props) $$invalidate(4, wordpress = $$props.wordpress);
    		if ('success' in $$props) $$invalidate(5, success = $$props.success);
    		if ('warning' in $$props) $$invalidate(6, warning = $$props.warning);
    		if ('error' in $$props) $$invalidate(7, error = $$props.error);
    		if ('heading' in $$props) $$invalidate(8, heading = $$props.heading);
    		if ('dismissible' in $$props) $$invalidate(9, dismissible = $$props.dismissible);
    		if ('icon' in $$props) $$invalidate(20, icon = $$props.icon);
    		if ('plainHeading' in $$props) $$invalidate(10, plainHeading = $$props.plainHeading);
    		if ('extra' in $$props) $$invalidate(11, extra = $$props.extra);
    		if ('links' in $$props) $$invalidate(21, links = $$props.links);
    		if ('expandable' in $$props) $$invalidate(12, expandable = $$props.expandable);
    		if ('expanded' in $$props) $$invalidate(1, expanded = $$props.expanded);
    		if ('$$scope' in $$props) $$invalidate(22, $$scope = $$props.$$scope);
    	};

    	$$self.$capture_state = () => ({
    		slide,
    		notifications,
    		strings,
    		urls,
    		notification,
    		unique_id,
    		inline,
    		wordpress,
    		success,
    		warning,
    		error,
    		info,
    		heading,
    		dismissible,
    		icon,
    		plainHeading,
    		extra,
    		links,
    		expandable,
    		expanded,
    		getIconURL,
    		iconHeight,
    		bodyHeight,
    		getLinksHTML,
    		linksHTML,
    		multiline,
    		iconURL,
    		$urls,
    		$strings
    	});

    	$$self.$inject_state = $$props => {
    		if ('notification' in $$props) $$invalidate(0, notification = $$props.notification);
    		if ('unique_id' in $$props) $$invalidate(2, unique_id = $$props.unique_id);
    		if ('inline' in $$props) $$invalidate(3, inline = $$props.inline);
    		if ('wordpress' in $$props) $$invalidate(4, wordpress = $$props.wordpress);
    		if ('success' in $$props) $$invalidate(5, success = $$props.success);
    		if ('warning' in $$props) $$invalidate(6, warning = $$props.warning);
    		if ('error' in $$props) $$invalidate(7, error = $$props.error);
    		if ('info' in $$props) $$invalidate(15, info = $$props.info);
    		if ('heading' in $$props) $$invalidate(8, heading = $$props.heading);
    		if ('dismissible' in $$props) $$invalidate(9, dismissible = $$props.dismissible);
    		if ('icon' in $$props) $$invalidate(20, icon = $$props.icon);
    		if ('plainHeading' in $$props) $$invalidate(10, plainHeading = $$props.plainHeading);
    		if ('extra' in $$props) $$invalidate(11, extra = $$props.extra);
    		if ('links' in $$props) $$invalidate(21, links = $$props.links);
    		if ('expandable' in $$props) $$invalidate(12, expandable = $$props.expandable);
    		if ('expanded' in $$props) $$invalidate(1, expanded = $$props.expanded);
    		if ('iconHeight' in $$props) $$invalidate(13, iconHeight = $$props.iconHeight);
    		if ('bodyHeight' in $$props) $$invalidate(14, bodyHeight = $$props.bodyHeight);
    		if ('linksHTML' in $$props) $$invalidate(16, linksHTML = $$props.linksHTML);
    		if ('multiline' in $$props) $$invalidate(17, multiline = $$props.multiline);
    		if ('iconURL' in $$props) $$invalidate(18, iconURL = $$props.iconURL);
    	};

    	if ($$props && "$$inject" in $$props) {
    		$$self.$inject_state($$props.$$inject);
    	}

    	$$self.$$.update = () => {
    		if ($$self.$$.dirty & /*icon, notification*/ 1048577) {
    			$$invalidate(18, iconURL = getIconURL(icon, notification.type));
    		}

    		if ($$self.$$.dirty & /*iconHeight, bodyHeight*/ 24576) {
    			$$invalidate(17, multiline = iconHeight && bodyHeight && bodyHeight > iconHeight);
    		}

    		if ($$self.$$.dirty & /*links*/ 2097152) {
    			$$invalidate(16, linksHTML = getLinksHTML(links));
    		}
    	};

    	return [
    		notification,
    		expanded,
    		unique_id,
    		inline,
    		wordpress,
    		success,
    		warning,
    		error,
    		heading,
    		dismissible,
    		plainHeading,
    		extra,
    		expandable,
    		iconHeight,
    		bodyHeight,
    		info,
    		linksHTML,
    		multiline,
    		iconURL,
    		$strings,
    		icon,
    		links,
    		$$scope,
    		slots,
    		div_elementresize_handler,
    		click_handler,
    		click_handler_1,
    		click_handler_2,
    		div0_elementresize_handler
    	];
    }

    class Notification extends SvelteComponentDev {
    	constructor(options) {
    		super(options);

    		init(this, options, instance$T, create_fragment$T, safe_not_equal, {
    			notification: 0,
    			unique_id: 2,
    			inline: 3,
    			wordpress: 4,
    			success: 5,
    			warning: 6,
    			error: 7,
    			heading: 8,
    			dismissible: 9,
    			icon: 20,
    			plainHeading: 10,
    			extra: 11,
    			links: 21,
    			expandable: 12,
    			expanded: 1
    		});

    		dispatch_dev("SvelteRegisterComponent", {
    			component: this,
    			tagName: "Notification",
    			options,
    			id: create_fragment$T.name
    		});
    	}

    	get notification() {
    		throw new Error("<Notification>: Props cannot be read directly from the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
    	}

    	set notification(value) {
    		throw new Error("<Notification>: Props cannot be set directly on the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
    	}

    	get unique_id() {
    		throw new Error("<Notification>: Props cannot be read directly from the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
    	}

    	set unique_id(value) {
    		throw new Error("<Notification>: Props cannot be set directly on the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
    	}

    	get inline() {
    		throw new Error("<Notification>: Props cannot be read directly from the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
    	}

    	set inline(value) {
    		throw new Error("<Notification>: Props cannot be set directly on the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
    	}

    	get wordpress() {
    		throw new Error("<Notification>: Props cannot be read directly from the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
    	}

    	set wordpress(value) {
    		throw new Error("<Notification>: Props cannot be set directly on the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
    	}

    	get success() {
    		throw new Error("<Notification>: Props cannot be read directly from the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
    	}

    	set success(value) {
    		throw new Error("<Notification>: Props cannot be set directly on the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
    	}

    	get warning() {
    		throw new Error("<Notification>: Props cannot be read directly from the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
    	}

    	set warning(value) {
    		throw new Error("<Notification>: Props cannot be set directly on the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
    	}

    	get error() {
    		throw new Error("<Notification>: Props cannot be read directly from the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
    	}

    	set error(value) {
    		throw new Error("<Notification>: Props cannot be set directly on the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
    	}

    	get heading() {
    		throw new Error("<Notification>: Props cannot be read directly from the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
    	}

    	set heading(value) {
    		throw new Error("<Notification>: Props cannot be set directly on the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
    	}

    	get dismissible() {
    		throw new Error("<Notification>: Props cannot be read directly from the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
    	}

    	set dismissible(value) {
    		throw new Error("<Notification>: Props cannot be set directly on the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
    	}

    	get icon() {
    		throw new Error("<Notification>: Props cannot be read directly from the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
    	}

    	set icon(value) {
    		throw new Error("<Notification>: Props cannot be set directly on the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
    	}

    	get plainHeading() {
    		throw new Error("<Notification>: Props cannot be read directly from the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
    	}

    	set plainHeading(value) {
    		throw new Error("<Notification>: Props cannot be set directly on the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
    	}

    	get extra() {
    		throw new Error("<Notification>: Props cannot be read directly from the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
    	}

    	set extra(value) {
    		throw new Error("<Notification>: Props cannot be set directly on the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
    	}

    	get links() {
    		throw new Error("<Notification>: Props cannot be read directly from the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
    	}

    	set links(value) {
    		throw new Error("<Notification>: Props cannot be set directly on the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
    	}

    	get expandable() {
    		throw new Error("<Notification>: Props cannot be read directly from the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
    	}

    	set expandable(value) {
    		throw new Error("<Notification>: Props cannot be set directly on the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
    	}

    	get expanded() {
    		throw new Error("<Notification>: Props cannot be read directly from the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
    	}

    	set expanded(value) {
    		throw new Error("<Notification>: Props cannot be set directly on the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
    	}
    }

    /* src/amazon-s3-and-cloudfront/ui/components/Notifications.svelte generated by Svelte v3.49.0 */
    const file$L = "src/amazon-s3-and-cloudfront/ui/components/Notifications.svelte";

    function get_each_context$8(ctx, list, i) {
    	const child_ctx = ctx.slice();
    	child_ctx[4] = list[i];
    	return child_ctx;
    }

    // (10:0) {#if $notifications.length}
    function create_if_block$o(ctx) {
    	let div;
    	let each_blocks = [];
    	let each_1_lookup = new Map();
    	let current;
    	let each_value = /*$notifications*/ ctx[3];
    	validate_each_argument(each_value);
    	const get_key = ctx => /*notification*/ ctx[4].render_key;
    	validate_each_keys(ctx, each_value, get_each_context$8, get_key);

    	for (let i = 0; i < each_value.length; i += 1) {
    		let child_ctx = get_each_context$8(ctx, each_value, i);
    		let key = get_key(child_ctx);
    		each_1_lookup.set(key, each_blocks[i] = create_each_block$8(key, child_ctx));
    	}

    	const block = {
    		c: function create() {
    			div = element("div");

    			for (let i = 0; i < each_blocks.length; i += 1) {
    				each_blocks[i].c();
    			}

    			attr_dev(div, "id", "notifications");
    			attr_dev(div, "class", "notifications wrapper");
    			add_location(div, file$L, 10, 1, 234);
    		},
    		m: function mount(target, anchor) {
    			insert_dev(target, div, anchor);

    			for (let i = 0; i < each_blocks.length; i += 1) {
    				each_blocks[i].m(div, null);
    			}

    			current = true;
    		},
    		p: function update(ctx, dirty) {
    			if (dirty & /*component, $notifications, tab, tabParent*/ 15) {
    				each_value = /*$notifications*/ ctx[3];
    				validate_each_argument(each_value);
    				group_outros();
    				validate_each_keys(ctx, each_value, get_each_context$8, get_key);
    				each_blocks = update_keyed_each(each_blocks, dirty, get_key, 1, ctx, each_value, each_1_lookup, div, outro_and_destroy_block, create_each_block$8, null, get_each_context$8);
    				check_outros();
    			}
    		},
    		i: function intro(local) {
    			if (current) return;

    			for (let i = 0; i < each_value.length; i += 1) {
    				transition_in(each_blocks[i]);
    			}

    			current = true;
    		},
    		o: function outro(local) {
    			for (let i = 0; i < each_blocks.length; i += 1) {
    				transition_out(each_blocks[i]);
    			}

    			current = false;
    		},
    		d: function destroy(detaching) {
    			if (detaching) detach_dev(div);

    			for (let i = 0; i < each_blocks.length; i += 1) {
    				each_blocks[i].d();
    			}
    		}
    	};

    	dispatch_dev("SvelteRegisterBlock", {
    		block,
    		id: create_if_block$o.name,
    		type: "if",
    		source: "(10:0) {#if $notifications.length}",
    		ctx
    	});

    	return block;
    }

    // (13:3) {#if !notification.dismissed && (notification.only_show_on_tab === tab || notification.only_show_on_tab === tabParent || !notification.only_show_on_tab)}
    function create_if_block_1$b(ctx) {
    	let switch_instance;
    	let switch_instance_anchor;
    	let current;
    	var switch_value = /*component*/ ctx[0];

    	function switch_props(ctx) {
    		return {
    			props: {
    				notification: /*notification*/ ctx[4],
    				$$slots: { default: [create_default_slot$q] },
    				$$scope: { ctx }
    			},
    			$$inline: true
    		};
    	}

    	if (switch_value) {
    		switch_instance = new switch_value(switch_props(ctx));
    	}

    	const block = {
    		c: function create() {
    			if (switch_instance) create_component(switch_instance.$$.fragment);
    			switch_instance_anchor = empty();
    		},
    		m: function mount(target, anchor) {
    			if (switch_instance) {
    				mount_component(switch_instance, target, anchor);
    			}

    			insert_dev(target, switch_instance_anchor, anchor);
    			current = true;
    		},
    		p: function update(ctx, dirty) {
    			const switch_instance_changes = {};
    			if (dirty & /*$notifications*/ 8) switch_instance_changes.notification = /*notification*/ ctx[4];

    			if (dirty & /*$$scope, $notifications*/ 136) {
    				switch_instance_changes.$$scope = { dirty, ctx };
    			}

    			if (switch_value !== (switch_value = /*component*/ ctx[0])) {
    				if (switch_instance) {
    					group_outros();
    					const old_component = switch_instance;

    					transition_out(old_component.$$.fragment, 1, 0, () => {
    						destroy_component(old_component, 1);
    					});

    					check_outros();
    				}

    				if (switch_value) {
    					switch_instance = new switch_value(switch_props(ctx));
    					create_component(switch_instance.$$.fragment);
    					transition_in(switch_instance.$$.fragment, 1);
    					mount_component(switch_instance, switch_instance_anchor.parentNode, switch_instance_anchor);
    				} else {
    					switch_instance = null;
    				}
    			} else if (switch_value) {
    				switch_instance.$set(switch_instance_changes);
    			}
    		},
    		i: function intro(local) {
    			if (current) return;
    			if (switch_instance) transition_in(switch_instance.$$.fragment, local);
    			current = true;
    		},
    		o: function outro(local) {
    			if (switch_instance) transition_out(switch_instance.$$.fragment, local);
    			current = false;
    		},
    		d: function destroy(detaching) {
    			if (detaching) detach_dev(switch_instance_anchor);
    			if (switch_instance) destroy_component(switch_instance, detaching);
    		}
    	};

    	dispatch_dev("SvelteRegisterBlock", {
    		block,
    		id: create_if_block_1$b.name,
    		type: "if",
    		source: "(13:3) {#if !notification.dismissed && (notification.only_show_on_tab === tab || notification.only_show_on_tab === tabParent || !notification.only_show_on_tab)}",
    		ctx
    	});

    	return block;
    }

    // (15:5) {#if notification.message}
    function create_if_block_2$7(ctx) {
    	let p;
    	let raw_value = /*notification*/ ctx[4].message + "";

    	const block = {
    		c: function create() {
    			p = element("p");
    			add_location(p, file$L, 15, 6, 619);
    		},
    		m: function mount(target, anchor) {
    			insert_dev(target, p, anchor);
    			p.innerHTML = raw_value;
    		},
    		p: function update(ctx, dirty) {
    			if (dirty & /*$notifications*/ 8 && raw_value !== (raw_value = /*notification*/ ctx[4].message + "")) p.innerHTML = raw_value;		},
    		d: function destroy(detaching) {
    			if (detaching) detach_dev(p);
    		}
    	};

    	dispatch_dev("SvelteRegisterBlock", {
    		block,
    		id: create_if_block_2$7.name,
    		type: "if",
    		source: "(15:5) {#if notification.message}",
    		ctx
    	});

    	return block;
    }

    // (14:4) <svelte:component this={component} notification={notification}>
    function create_default_slot$q(ctx) {
    	let t;
    	let if_block = /*notification*/ ctx[4].message && create_if_block_2$7(ctx);

    	const block = {
    		c: function create() {
    			if (if_block) if_block.c();
    			t = space();
    		},
    		m: function mount(target, anchor) {
    			if (if_block) if_block.m(target, anchor);
    			insert_dev(target, t, anchor);
    		},
    		p: function update(ctx, dirty) {
    			if (/*notification*/ ctx[4].message) {
    				if (if_block) {
    					if_block.p(ctx, dirty);
    				} else {
    					if_block = create_if_block_2$7(ctx);
    					if_block.c();
    					if_block.m(t.parentNode, t);
    				}
    			} else if (if_block) {
    				if_block.d(1);
    				if_block = null;
    			}
    		},
    		d: function destroy(detaching) {
    			if (if_block) if_block.d(detaching);
    			if (detaching) detach_dev(t);
    		}
    	};

    	dispatch_dev("SvelteRegisterBlock", {
    		block,
    		id: create_default_slot$q.name,
    		type: "slot",
    		source: "(14:4) <svelte:component this={component} notification={notification}>",
    		ctx
    	});

    	return block;
    }

    // (12:2) {#each $notifications as notification (notification.render_key)}
    function create_each_block$8(key_1, ctx) {
    	let first;
    	let if_block_anchor;
    	let current;
    	let if_block = !/*notification*/ ctx[4].dismissed && (/*notification*/ ctx[4].only_show_on_tab === /*tab*/ ctx[1] || /*notification*/ ctx[4].only_show_on_tab === /*tabParent*/ ctx[2] || !/*notification*/ ctx[4].only_show_on_tab) && create_if_block_1$b(ctx);

    	const block = {
    		key: key_1,
    		first: null,
    		c: function create() {
    			first = empty();
    			if (if_block) if_block.c();
    			if_block_anchor = empty();
    			this.first = first;
    		},
    		m: function mount(target, anchor) {
    			insert_dev(target, first, anchor);
    			if (if_block) if_block.m(target, anchor);
    			insert_dev(target, if_block_anchor, anchor);
    			current = true;
    		},
    		p: function update(new_ctx, dirty) {
    			ctx = new_ctx;

    			if (!/*notification*/ ctx[4].dismissed && (/*notification*/ ctx[4].only_show_on_tab === /*tab*/ ctx[1] || /*notification*/ ctx[4].only_show_on_tab === /*tabParent*/ ctx[2] || !/*notification*/ ctx[4].only_show_on_tab)) {
    				if (if_block) {
    					if_block.p(ctx, dirty);

    					if (dirty & /*$notifications, tab, tabParent*/ 14) {
    						transition_in(if_block, 1);
    					}
    				} else {
    					if_block = create_if_block_1$b(ctx);
    					if_block.c();
    					transition_in(if_block, 1);
    					if_block.m(if_block_anchor.parentNode, if_block_anchor);
    				}
    			} else if (if_block) {
    				group_outros();

    				transition_out(if_block, 1, 1, () => {
    					if_block = null;
    				});

    				check_outros();
    			}
    		},
    		i: function intro(local) {
    			if (current) return;
    			transition_in(if_block);
    			current = true;
    		},
    		o: function outro(local) {
    			transition_out(if_block);
    			current = false;
    		},
    		d: function destroy(detaching) {
    			if (detaching) detach_dev(first);
    			if (if_block) if_block.d(detaching);
    			if (detaching) detach_dev(if_block_anchor);
    		}
    	};

    	dispatch_dev("SvelteRegisterBlock", {
    		block,
    		id: create_each_block$8.name,
    		type: "each",
    		source: "(12:2) {#each $notifications as notification (notification.render_key)}",
    		ctx
    	});

    	return block;
    }

    function create_fragment$S(ctx) {
    	let if_block_anchor;
    	let current;
    	let if_block = /*$notifications*/ ctx[3].length && create_if_block$o(ctx);

    	const block = {
    		c: function create() {
    			if (if_block) if_block.c();
    			if_block_anchor = empty();
    		},
    		l: function claim(nodes) {
    			throw new Error("options.hydrate only works if the component was compiled with the `hydratable: true` option");
    		},
    		m: function mount(target, anchor) {
    			if (if_block) if_block.m(target, anchor);
    			insert_dev(target, if_block_anchor, anchor);
    			current = true;
    		},
    		p: function update(ctx, [dirty]) {
    			if (/*$notifications*/ ctx[3].length) {
    				if (if_block) {
    					if_block.p(ctx, dirty);

    					if (dirty & /*$notifications*/ 8) {
    						transition_in(if_block, 1);
    					}
    				} else {
    					if_block = create_if_block$o(ctx);
    					if_block.c();
    					transition_in(if_block, 1);
    					if_block.m(if_block_anchor.parentNode, if_block_anchor);
    				}
    			} else if (if_block) {
    				group_outros();

    				transition_out(if_block, 1, 1, () => {
    					if_block = null;
    				});

    				check_outros();
    			}
    		},
    		i: function intro(local) {
    			if (current) return;
    			transition_in(if_block);
    			current = true;
    		},
    		o: function outro(local) {
    			transition_out(if_block);
    			current = false;
    		},
    		d: function destroy(detaching) {
    			if (if_block) if_block.d(detaching);
    			if (detaching) detach_dev(if_block_anchor);
    		}
    	};

    	dispatch_dev("SvelteRegisterBlock", {
    		block,
    		id: create_fragment$S.name,
    		type: "component",
    		source: "",
    		ctx
    	});

    	return block;
    }

    function instance$S($$self, $$props, $$invalidate) {
    	let $notifications;
    	validate_store(notifications, 'notifications');
    	component_subscribe($$self, notifications, $$value => $$invalidate(3, $notifications = $$value));
    	let { $$slots: slots = {}, $$scope } = $$props;
    	validate_slots('Notifications', slots, []);
    	let { component = Notification } = $$props;
    	let { tab = "" } = $$props;
    	let { tabParent = "" } = $$props;
    	const writable_props = ['component', 'tab', 'tabParent'];

    	Object.keys($$props).forEach(key => {
    		if (!~writable_props.indexOf(key) && key.slice(0, 2) !== '$$' && key !== 'slot') console.warn(`<Notifications> was created with unknown prop '${key}'`);
    	});

    	$$self.$$set = $$props => {
    		if ('component' in $$props) $$invalidate(0, component = $$props.component);
    		if ('tab' in $$props) $$invalidate(1, tab = $$props.tab);
    		if ('tabParent' in $$props) $$invalidate(2, tabParent = $$props.tabParent);
    	};

    	$$self.$capture_state = () => ({
    		notifications,
    		Notification,
    		component,
    		tab,
    		tabParent,
    		$notifications
    	});

    	$$self.$inject_state = $$props => {
    		if ('component' in $$props) $$invalidate(0, component = $$props.component);
    		if ('tab' in $$props) $$invalidate(1, tab = $$props.tab);
    		if ('tabParent' in $$props) $$invalidate(2, tabParent = $$props.tabParent);
    	};

    	if ($$props && "$$inject" in $$props) {
    		$$self.$inject_state($$props.$$inject);
    	}

    	return [component, tab, tabParent, $notifications];
    }

    class Notifications extends SvelteComponentDev {
    	constructor(options) {
    		super(options);
    		init(this, options, instance$S, create_fragment$S, safe_not_equal, { component: 0, tab: 1, tabParent: 2 });

    		dispatch_dev("SvelteRegisterComponent", {
    			component: this,
    			tagName: "Notifications",
    			options,
    			id: create_fragment$S.name
    		});
    	}

    	get component() {
    		throw new Error("<Notifications>: Props cannot be read directly from the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
    	}

    	set component(value) {
    		throw new Error("<Notifications>: Props cannot be set directly on the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
    	}

    	get tab() {
    		throw new Error("<Notifications>: Props cannot be read directly from the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
    	}

    	set tab(value) {
    		throw new Error("<Notifications>: Props cannot be set directly on the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
    	}

    	get tabParent() {
    		throw new Error("<Notifications>: Props cannot be read directly from the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
    	}

    	set tabParent(value) {
    		throw new Error("<Notifications>: Props cannot be set directly on the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
    	}
    }

    /* src/amazon-s3-and-cloudfront/ui/components/BlockPublicAccessWarning.svelte generated by Svelte v3.49.0 */
    const file$K = "src/amazon-s3-and-cloudfront/ui/components/BlockPublicAccessWarning.svelte";

    // (6:0) {#if $bapa && !$delivery_provider.block_public_access_supported}
    function create_if_block$n(ctx) {
    	let notification;
    	let current;

    	notification = new Notification({
    			props: {
    				warning: true,
    				heading: /*$storage_provider*/ ctx[2].block_public_access_warning.heading,
    				$$slots: { default: [create_default_slot$p] },
    				$$scope: { ctx }
    			},
    			$$inline: true
    		});

    	const block = {
    		c: function create() {
    			create_component(notification.$$.fragment);
    		},
    		m: function mount(target, anchor) {
    			mount_component(notification, target, anchor);
    			current = true;
    		},
    		p: function update(ctx, dirty) {
    			const notification_changes = {};
    			if (dirty & /*$storage_provider*/ 4) notification_changes.heading = /*$storage_provider*/ ctx[2].block_public_access_warning.heading;

    			if (dirty & /*$$scope, $storage_provider*/ 12) {
    				notification_changes.$$scope = { dirty, ctx };
    			}

    			notification.$set(notification_changes);
    		},
    		i: function intro(local) {
    			if (current) return;
    			transition_in(notification.$$.fragment, local);
    			current = true;
    		},
    		o: function outro(local) {
    			transition_out(notification.$$.fragment, local);
    			current = false;
    		},
    		d: function destroy(detaching) {
    			destroy_component(notification, detaching);
    		}
    	};

    	dispatch_dev("SvelteRegisterBlock", {
    		block,
    		id: create_if_block$n.name,
    		type: "if",
    		source: "(6:0) {#if $bapa && !$delivery_provider.block_public_access_supported}",
    		ctx
    	});

    	return block;
    }

    // (7:1) <Notification warning heading={$storage_provider.block_public_access_warning.heading}>
    function create_default_slot$p(ctx) {
    	let p;
    	let raw_value = /*$storage_provider*/ ctx[2].block_public_access_warning.message + "";

    	const block = {
    		c: function create() {
    			p = element("p");
    			add_location(p, file$K, 7, 2, 299);
    		},
    		m: function mount(target, anchor) {
    			insert_dev(target, p, anchor);
    			p.innerHTML = raw_value;
    		},
    		p: function update(ctx, dirty) {
    			if (dirty & /*$storage_provider*/ 4 && raw_value !== (raw_value = /*$storage_provider*/ ctx[2].block_public_access_warning.message + "")) p.innerHTML = raw_value;		},
    		d: function destroy(detaching) {
    			if (detaching) detach_dev(p);
    		}
    	};

    	dispatch_dev("SvelteRegisterBlock", {
    		block,
    		id: create_default_slot$p.name,
    		type: "slot",
    		source: "(7:1) <Notification warning heading={$storage_provider.block_public_access_warning.heading}>",
    		ctx
    	});

    	return block;
    }

    function create_fragment$R(ctx) {
    	let if_block_anchor;
    	let current;
    	let if_block = /*$bapa*/ ctx[0] && !/*$delivery_provider*/ ctx[1].block_public_access_supported && create_if_block$n(ctx);

    	const block = {
    		c: function create() {
    			if (if_block) if_block.c();
    			if_block_anchor = empty();
    		},
    		l: function claim(nodes) {
    			throw new Error("options.hydrate only works if the component was compiled with the `hydratable: true` option");
    		},
    		m: function mount(target, anchor) {
    			if (if_block) if_block.m(target, anchor);
    			insert_dev(target, if_block_anchor, anchor);
    			current = true;
    		},
    		p: function update(ctx, [dirty]) {
    			if (/*$bapa*/ ctx[0] && !/*$delivery_provider*/ ctx[1].block_public_access_supported) {
    				if (if_block) {
    					if_block.p(ctx, dirty);

    					if (dirty & /*$bapa, $delivery_provider*/ 3) {
    						transition_in(if_block, 1);
    					}
    				} else {
    					if_block = create_if_block$n(ctx);
    					if_block.c();
    					transition_in(if_block, 1);
    					if_block.m(if_block_anchor.parentNode, if_block_anchor);
    				}
    			} else if (if_block) {
    				group_outros();

    				transition_out(if_block, 1, 1, () => {
    					if_block = null;
    				});

    				check_outros();
    			}
    		},
    		i: function intro(local) {
    			if (current) return;
    			transition_in(if_block);
    			current = true;
    		},
    		o: function outro(local) {
    			transition_out(if_block);
    			current = false;
    		},
    		d: function destroy(detaching) {
    			if (if_block) if_block.d(detaching);
    			if (detaching) detach_dev(if_block_anchor);
    		}
    	};

    	dispatch_dev("SvelteRegisterBlock", {
    		block,
    		id: create_fragment$R.name,
    		type: "component",
    		source: "",
    		ctx
    	});

    	return block;
    }

    function instance$R($$self, $$props, $$invalidate) {
    	let $bapa;
    	let $delivery_provider;
    	let $storage_provider;
    	validate_store(bapa, 'bapa');
    	component_subscribe($$self, bapa, $$value => $$invalidate(0, $bapa = $$value));
    	validate_store(delivery_provider, 'delivery_provider');
    	component_subscribe($$self, delivery_provider, $$value => $$invalidate(1, $delivery_provider = $$value));
    	validate_store(storage_provider, 'storage_provider');
    	component_subscribe($$self, storage_provider, $$value => $$invalidate(2, $storage_provider = $$value));
    	let { $$slots: slots = {}, $$scope } = $$props;
    	validate_slots('BlockPublicAccessWarning', slots, []);
    	const writable_props = [];

    	Object.keys($$props).forEach(key => {
    		if (!~writable_props.indexOf(key) && key.slice(0, 2) !== '$$' && key !== 'slot') console.warn(`<BlockPublicAccessWarning> was created with unknown prop '${key}'`);
    	});

    	$$self.$capture_state = () => ({
    		bapa,
    		storage_provider,
    		delivery_provider,
    		Notification,
    		$bapa,
    		$delivery_provider,
    		$storage_provider
    	});

    	return [$bapa, $delivery_provider, $storage_provider];
    }

    class BlockPublicAccessWarning extends SvelteComponentDev {
    	constructor(options) {
    		super(options);
    		init(this, options, instance$R, create_fragment$R, safe_not_equal, {});

    		dispatch_dev("SvelteRegisterComponent", {
    			component: this,
    			tagName: "BlockPublicAccessWarning",
    			options,
    			id: create_fragment$R.name
    		});
    	}
    }

    /* src/amazon-s3-and-cloudfront/ui/components/ObjectOwnershipEnforcedWarning.svelte generated by Svelte v3.49.0 */
    const file$J = "src/amazon-s3-and-cloudfront/ui/components/ObjectOwnershipEnforcedWarning.svelte";

    // (6:0) {#if $ooe && !$delivery_provider.object_ownership_supported}
    function create_if_block$m(ctx) {
    	let notification;
    	let current;

    	notification = new Notification({
    			props: {
    				warning: true,
    				heading: /*$storage_provider*/ ctx[2].object_ownership_enforced_warning.heading,
    				$$slots: { default: [create_default_slot$o] },
    				$$scope: { ctx }
    			},
    			$$inline: true
    		});

    	const block = {
    		c: function create() {
    			create_component(notification.$$.fragment);
    		},
    		m: function mount(target, anchor) {
    			mount_component(notification, target, anchor);
    			current = true;
    		},
    		p: function update(ctx, dirty) {
    			const notification_changes = {};
    			if (dirty & /*$storage_provider*/ 4) notification_changes.heading = /*$storage_provider*/ ctx[2].object_ownership_enforced_warning.heading;

    			if (dirty & /*$$scope, $storage_provider*/ 12) {
    				notification_changes.$$scope = { dirty, ctx };
    			}

    			notification.$set(notification_changes);
    		},
    		i: function intro(local) {
    			if (current) return;
    			transition_in(notification.$$.fragment, local);
    			current = true;
    		},
    		o: function outro(local) {
    			transition_out(notification.$$.fragment, local);
    			current = false;
    		},
    		d: function destroy(detaching) {
    			destroy_component(notification, detaching);
    		}
    	};

    	dispatch_dev("SvelteRegisterBlock", {
    		block,
    		id: create_if_block$m.name,
    		type: "if",
    		source: "(6:0) {#if $ooe && !$delivery_provider.object_ownership_supported}",
    		ctx
    	});

    	return block;
    }

    // (7:1) <Notification warning heading={$storage_provider.object_ownership_enforced_warning.heading}>
    function create_default_slot$o(ctx) {
    	let p;
    	let raw_value = /*$storage_provider*/ ctx[2].object_ownership_enforced_warning.message + "";

    	const block = {
    		c: function create() {
    			p = element("p");
    			add_location(p, file$J, 7, 2, 300);
    		},
    		m: function mount(target, anchor) {
    			insert_dev(target, p, anchor);
    			p.innerHTML = raw_value;
    		},
    		p: function update(ctx, dirty) {
    			if (dirty & /*$storage_provider*/ 4 && raw_value !== (raw_value = /*$storage_provider*/ ctx[2].object_ownership_enforced_warning.message + "")) p.innerHTML = raw_value;		},
    		d: function destroy(detaching) {
    			if (detaching) detach_dev(p);
    		}
    	};

    	dispatch_dev("SvelteRegisterBlock", {
    		block,
    		id: create_default_slot$o.name,
    		type: "slot",
    		source: "(7:1) <Notification warning heading={$storage_provider.object_ownership_enforced_warning.heading}>",
    		ctx
    	});

    	return block;
    }

    function create_fragment$Q(ctx) {
    	let if_block_anchor;
    	let current;
    	let if_block = /*$ooe*/ ctx[0] && !/*$delivery_provider*/ ctx[1].object_ownership_supported && create_if_block$m(ctx);

    	const block = {
    		c: function create() {
    			if (if_block) if_block.c();
    			if_block_anchor = empty();
    		},
    		l: function claim(nodes) {
    			throw new Error("options.hydrate only works if the component was compiled with the `hydratable: true` option");
    		},
    		m: function mount(target, anchor) {
    			if (if_block) if_block.m(target, anchor);
    			insert_dev(target, if_block_anchor, anchor);
    			current = true;
    		},
    		p: function update(ctx, [dirty]) {
    			if (/*$ooe*/ ctx[0] && !/*$delivery_provider*/ ctx[1].object_ownership_supported) {
    				if (if_block) {
    					if_block.p(ctx, dirty);

    					if (dirty & /*$ooe, $delivery_provider*/ 3) {
    						transition_in(if_block, 1);
    					}
    				} else {
    					if_block = create_if_block$m(ctx);
    					if_block.c();
    					transition_in(if_block, 1);
    					if_block.m(if_block_anchor.parentNode, if_block_anchor);
    				}
    			} else if (if_block) {
    				group_outros();

    				transition_out(if_block, 1, 1, () => {
    					if_block = null;
    				});

    				check_outros();
    			}
    		},
    		i: function intro(local) {
    			if (current) return;
    			transition_in(if_block);
    			current = true;
    		},
    		o: function outro(local) {
    			transition_out(if_block);
    			current = false;
    		},
    		d: function destroy(detaching) {
    			if (if_block) if_block.d(detaching);
    			if (detaching) detach_dev(if_block_anchor);
    		}
    	};

    	dispatch_dev("SvelteRegisterBlock", {
    		block,
    		id: create_fragment$Q.name,
    		type: "component",
    		source: "",
    		ctx
    	});

    	return block;
    }

    function instance$Q($$self, $$props, $$invalidate) {
    	let $ooe;
    	let $delivery_provider;
    	let $storage_provider;
    	validate_store(ooe, 'ooe');
    	component_subscribe($$self, ooe, $$value => $$invalidate(0, $ooe = $$value));
    	validate_store(delivery_provider, 'delivery_provider');
    	component_subscribe($$self, delivery_provider, $$value => $$invalidate(1, $delivery_provider = $$value));
    	validate_store(storage_provider, 'storage_provider');
    	component_subscribe($$self, storage_provider, $$value => $$invalidate(2, $storage_provider = $$value));
    	let { $$slots: slots = {}, $$scope } = $$props;
    	validate_slots('ObjectOwnershipEnforcedWarning', slots, []);
    	const writable_props = [];

    	Object.keys($$props).forEach(key => {
    		if (!~writable_props.indexOf(key) && key.slice(0, 2) !== '$$' && key !== 'slot') console.warn(`<ObjectOwnershipEnforcedWarning> was created with unknown prop '${key}'`);
    	});

    	$$self.$capture_state = () => ({
    		ooe,
    		storage_provider,
    		delivery_provider,
    		Notification,
    		$ooe,
    		$delivery_provider,
    		$storage_provider
    	});

    	return [$ooe, $delivery_provider, $storage_provider];
    }

    class ObjectOwnershipEnforcedWarning extends SvelteComponentDev {
    	constructor(options) {
    		super(options);
    		init(this, options, instance$Q, create_fragment$Q, safe_not_equal, {});

    		dispatch_dev("SvelteRegisterComponent", {
    			component: this,
    			tagName: "ObjectOwnershipEnforcedWarning",
    			options,
    			id: create_fragment$Q.name
    		});
    	}
    }

    /* src/amazon-s3-and-cloudfront/ui/components/SubNavItem.svelte generated by Svelte v3.49.0 */
    const file$I = "src/amazon-s3-and-cloudfront/ui/components/SubNavItem.svelte";

    function create_fragment$P(ctx) {
    	let li;
    	let a;
    	let t_value = /*page*/ ctx[0].title() + "";
    	let t;
    	let a_href_value;
    	let a_title_value;
    	let mounted;
    	let dispose;

    	const block = {
    		c: function create() {
    			li = element("li");
    			a = element("a");
    			t = text(t_value);
    			attr_dev(a, "href", a_href_value = /*page*/ ctx[0].route);
    			attr_dev(a, "title", a_title_value = /*page*/ ctx[0].title());
    			add_location(a, file$I, 10, 1, 221);
    			attr_dev(li, "class", "subnav-item");
    			toggle_class(li, "active", /*$location*/ ctx[3] === /*page*/ ctx[0].route);
    			toggle_class(li, "focus", /*focus*/ ctx[1]);
    			toggle_class(li, "hover", /*hover*/ ctx[2]);
    			add_location(li, file$I, 9, 0, 131);
    		},
    		l: function claim(nodes) {
    			throw new Error("options.hydrate only works if the component was compiled with the `hydratable: true` option");
    		},
    		m: function mount(target, anchor) {
    			insert_dev(target, li, anchor);
    			append_dev(li, a);
    			append_dev(a, t);

    			if (!mounted) {
    				dispose = [
    					action_destroyer(link.call(null, a)),
    					listen_dev(a, "focusin", /*focusin_handler*/ ctx[4], false, false, false),
    					listen_dev(a, "focusout", /*focusout_handler*/ ctx[5], false, false, false),
    					listen_dev(a, "mouseenter", /*mouseenter_handler*/ ctx[6], false, false, false),
    					listen_dev(a, "mouseleave", /*mouseleave_handler*/ ctx[7], false, false, false)
    				];

    				mounted = true;
    			}
    		},
    		p: function update(ctx, [dirty]) {
    			if (dirty & /*page*/ 1 && t_value !== (t_value = /*page*/ ctx[0].title() + "")) set_data_dev(t, t_value);

    			if (dirty & /*page*/ 1 && a_href_value !== (a_href_value = /*page*/ ctx[0].route)) {
    				attr_dev(a, "href", a_href_value);
    			}

    			if (dirty & /*page*/ 1 && a_title_value !== (a_title_value = /*page*/ ctx[0].title())) {
    				attr_dev(a, "title", a_title_value);
    			}

    			if (dirty & /*$location, page*/ 9) {
    				toggle_class(li, "active", /*$location*/ ctx[3] === /*page*/ ctx[0].route);
    			}

    			if (dirty & /*focus*/ 2) {
    				toggle_class(li, "focus", /*focus*/ ctx[1]);
    			}

    			if (dirty & /*hover*/ 4) {
    				toggle_class(li, "hover", /*hover*/ ctx[2]);
    			}
    		},
    		i: noop,
    		o: noop,
    		d: function destroy(detaching) {
    			if (detaching) detach_dev(li);
    			mounted = false;
    			run_all(dispose);
    		}
    	};

    	dispatch_dev("SvelteRegisterBlock", {
    		block,
    		id: create_fragment$P.name,
    		type: "component",
    		source: "",
    		ctx
    	});

    	return block;
    }

    function instance$P($$self, $$props, $$invalidate) {
    	let $location;
    	validate_store(location$1, 'location');
    	component_subscribe($$self, location$1, $$value => $$invalidate(3, $location = $$value));
    	let { $$slots: slots = {}, $$scope } = $$props;
    	validate_slots('SubNavItem', slots, []);
    	let { page } = $$props;
    	let focus = false;
    	let hover = false;
    	const writable_props = ['page'];

    	Object.keys($$props).forEach(key => {
    		if (!~writable_props.indexOf(key) && key.slice(0, 2) !== '$$' && key !== 'slot') console.warn(`<SubNavItem> was created with unknown prop '${key}'`);
    	});

    	const focusin_handler = () => $$invalidate(1, focus = true);
    	const focusout_handler = () => $$invalidate(1, focus = false);
    	const mouseenter_handler = () => $$invalidate(2, hover = true);
    	const mouseleave_handler = () => $$invalidate(2, hover = false);

    	$$self.$$set = $$props => {
    		if ('page' in $$props) $$invalidate(0, page = $$props.page);
    	};

    	$$self.$capture_state = () => ({
    		link,
    		location: location$1,
    		page,
    		focus,
    		hover,
    		$location
    	});

    	$$self.$inject_state = $$props => {
    		if ('page' in $$props) $$invalidate(0, page = $$props.page);
    		if ('focus' in $$props) $$invalidate(1, focus = $$props.focus);
    		if ('hover' in $$props) $$invalidate(2, hover = $$props.hover);
    	};

    	if ($$props && "$$inject" in $$props) {
    		$$self.$inject_state($$props.$$inject);
    	}

    	return [
    		page,
    		focus,
    		hover,
    		$location,
    		focusin_handler,
    		focusout_handler,
    		mouseenter_handler,
    		mouseleave_handler
    	];
    }

    class SubNavItem extends SvelteComponentDev {
    	constructor(options) {
    		super(options);
    		init(this, options, instance$P, create_fragment$P, safe_not_equal, { page: 0 });

    		dispatch_dev("SvelteRegisterComponent", {
    			component: this,
    			tagName: "SubNavItem",
    			options,
    			id: create_fragment$P.name
    		});

    		const { ctx } = this.$$;
    		const props = options.props || {};

    		if (/*page*/ ctx[0] === undefined && !('page' in props)) {
    			console.warn("<SubNavItem> was created without expected prop 'page'");
    		}
    	}

    	get page() {
    		throw new Error("<SubNavItem>: Props cannot be read directly from the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
    	}

    	set page(value) {
    		throw new Error("<SubNavItem>: Props cannot be set directly on the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
    	}
    }

    /* src/amazon-s3-and-cloudfront/ui/components/SubNav.svelte generated by Svelte v3.49.0 */
    const file$H = "src/amazon-s3-and-cloudfront/ui/components/SubNav.svelte";

    function get_each_context$7(ctx, list, i) {
    	const child_ctx = ctx.slice();
    	child_ctx[6] = list[i];
    	child_ctx[8] = i;
    	return child_ctx;
    }

    // (13:0) {#if displayItems}
    function create_if_block$l(ctx) {
    	let ul;
    	let ul_class_value;
    	let current;
    	let each_value = /*displayItems*/ ctx[3];
    	validate_each_argument(each_value);
    	let each_blocks = [];

    	for (let i = 0; i < each_value.length; i += 1) {
    		each_blocks[i] = create_each_block$7(get_each_context$7(ctx, each_value, i));
    	}

    	const out = i => transition_out(each_blocks[i], 1, 1, () => {
    		each_blocks[i] = null;
    	});

    	const block = {
    		c: function create() {
    			ul = element("ul");

    			for (let i = 0; i < each_blocks.length; i += 1) {
    				each_blocks[i].c();
    			}

    			attr_dev(ul, "class", ul_class_value = "subnav " + /*name*/ ctx[0]);
    			toggle_class(ul, "subpage", /*subpage*/ ctx[1]);
    			toggle_class(ul, "progress", /*progress*/ ctx[2]);
    			add_location(ul, file$H, 13, 1, 361);
    		},
    		m: function mount(target, anchor) {
    			insert_dev(target, ul, anchor);

    			for (let i = 0; i < each_blocks.length; i += 1) {
    				each_blocks[i].m(ul, null);
    			}

    			current = true;
    		},
    		p: function update(ctx, dirty) {
    			if (dirty & /*$urls, progress, displayItems*/ 28) {
    				each_value = /*displayItems*/ ctx[3];
    				validate_each_argument(each_value);
    				let i;

    				for (i = 0; i < each_value.length; i += 1) {
    					const child_ctx = get_each_context$7(ctx, each_value, i);

    					if (each_blocks[i]) {
    						each_blocks[i].p(child_ctx, dirty);
    						transition_in(each_blocks[i], 1);
    					} else {
    						each_blocks[i] = create_each_block$7(child_ctx);
    						each_blocks[i].c();
    						transition_in(each_blocks[i], 1);
    						each_blocks[i].m(ul, null);
    					}
    				}

    				group_outros();

    				for (i = each_value.length; i < each_blocks.length; i += 1) {
    					out(i);
    				}

    				check_outros();
    			}

    			if (!current || dirty & /*name*/ 1 && ul_class_value !== (ul_class_value = "subnav " + /*name*/ ctx[0])) {
    				attr_dev(ul, "class", ul_class_value);
    			}

    			if (dirty & /*name, subpage*/ 3) {
    				toggle_class(ul, "subpage", /*subpage*/ ctx[1]);
    			}

    			if (dirty & /*name, progress*/ 5) {
    				toggle_class(ul, "progress", /*progress*/ ctx[2]);
    			}
    		},
    		i: function intro(local) {
    			if (current) return;

    			for (let i = 0; i < each_value.length; i += 1) {
    				transition_in(each_blocks[i]);
    			}

    			current = true;
    		},
    		o: function outro(local) {
    			each_blocks = each_blocks.filter(Boolean);

    			for (let i = 0; i < each_blocks.length; i += 1) {
    				transition_out(each_blocks[i]);
    			}

    			current = false;
    		},
    		d: function destroy(detaching) {
    			if (detaching) detach_dev(ul);
    			destroy_each(each_blocks, detaching);
    		}
    	};

    	dispatch_dev("SvelteRegisterBlock", {
    		block,
    		id: create_if_block$l.name,
    		type: "if",
    		source: "(13:0) {#if displayItems}",
    		ctx
    	});

    	return block;
    }

    // (18:3) {#if progress && index < (displayItems.length - 1)}
    function create_if_block_1$a(ctx) {
    	let li;
    	let img;
    	let img_src_value;
    	let t;

    	const block = {
    		c: function create() {
    			li = element("li");
    			img = element("img");
    			t = space();
    			if (!src_url_equal(img.src, img_src_value = /*$urls*/ ctx[4].assets + 'img/icon/subnav-arrow.svg')) attr_dev(img, "src", img_src_value);
    			attr_dev(img, "alt", "");
    			add_location(img, file$H, 19, 5, 634);
    			attr_dev(li, "class", "step-arrow");
    			add_location(li, file$H, 18, 4, 605);
    		},
    		m: function mount(target, anchor) {
    			insert_dev(target, li, anchor);
    			append_dev(li, img);
    			append_dev(li, t);
    		},
    		p: function update(ctx, dirty) {
    			if (dirty & /*$urls*/ 16 && !src_url_equal(img.src, img_src_value = /*$urls*/ ctx[4].assets + 'img/icon/subnav-arrow.svg')) {
    				attr_dev(img, "src", img_src_value);
    			}
    		},
    		d: function destroy(detaching) {
    			if (detaching) detach_dev(li);
    		}
    	};

    	dispatch_dev("SvelteRegisterBlock", {
    		block,
    		id: create_if_block_1$a.name,
    		type: "if",
    		source: "(18:3) {#if progress && index < (displayItems.length - 1)}",
    		ctx
    	});

    	return block;
    }

    // (15:2) {#each displayItems as page, index}
    function create_each_block$7(ctx) {
    	let subnavitem;
    	let t;
    	let if_block_anchor;
    	let current;

    	subnavitem = new SubNavItem({
    			props: { page: /*page*/ ctx[6] },
    			$$inline: true
    		});

    	let if_block = /*progress*/ ctx[2] && /*index*/ ctx[8] < /*displayItems*/ ctx[3].length - 1 && create_if_block_1$a(ctx);

    	const block = {
    		c: function create() {
    			create_component(subnavitem.$$.fragment);
    			t = space();
    			if (if_block) if_block.c();
    			if_block_anchor = empty();
    		},
    		m: function mount(target, anchor) {
    			mount_component(subnavitem, target, anchor);
    			insert_dev(target, t, anchor);
    			if (if_block) if_block.m(target, anchor);
    			insert_dev(target, if_block_anchor, anchor);
    			current = true;
    		},
    		p: function update(ctx, dirty) {
    			const subnavitem_changes = {};
    			if (dirty & /*displayItems*/ 8) subnavitem_changes.page = /*page*/ ctx[6];
    			subnavitem.$set(subnavitem_changes);

    			if (/*progress*/ ctx[2] && /*index*/ ctx[8] < /*displayItems*/ ctx[3].length - 1) {
    				if (if_block) {
    					if_block.p(ctx, dirty);
    				} else {
    					if_block = create_if_block_1$a(ctx);
    					if_block.c();
    					if_block.m(if_block_anchor.parentNode, if_block_anchor);
    				}
    			} else if (if_block) {
    				if_block.d(1);
    				if_block = null;
    			}
    		},
    		i: function intro(local) {
    			if (current) return;
    			transition_in(subnavitem.$$.fragment, local);
    			current = true;
    		},
    		o: function outro(local) {
    			transition_out(subnavitem.$$.fragment, local);
    			current = false;
    		},
    		d: function destroy(detaching) {
    			destroy_component(subnavitem, detaching);
    			if (detaching) detach_dev(t);
    			if (if_block) if_block.d(detaching);
    			if (detaching) detach_dev(if_block_anchor);
    		}
    	};

    	dispatch_dev("SvelteRegisterBlock", {
    		block,
    		id: create_each_block$7.name,
    		type: "each",
    		source: "(15:2) {#each displayItems as page, index}",
    		ctx
    	});

    	return block;
    }

    function create_fragment$O(ctx) {
    	let if_block_anchor;
    	let current;
    	let if_block = /*displayItems*/ ctx[3] && create_if_block$l(ctx);

    	const block = {
    		c: function create() {
    			if (if_block) if_block.c();
    			if_block_anchor = empty();
    		},
    		l: function claim(nodes) {
    			throw new Error("options.hydrate only works if the component was compiled with the `hydratable: true` option");
    		},
    		m: function mount(target, anchor) {
    			if (if_block) if_block.m(target, anchor);
    			insert_dev(target, if_block_anchor, anchor);
    			current = true;
    		},
    		p: function update(ctx, [dirty]) {
    			if (/*displayItems*/ ctx[3]) {
    				if (if_block) {
    					if_block.p(ctx, dirty);

    					if (dirty & /*displayItems*/ 8) {
    						transition_in(if_block, 1);
    					}
    				} else {
    					if_block = create_if_block$l(ctx);
    					if_block.c();
    					transition_in(if_block, 1);
    					if_block.m(if_block_anchor.parentNode, if_block_anchor);
    				}
    			} else if (if_block) {
    				group_outros();

    				transition_out(if_block, 1, 1, () => {
    					if_block = null;
    				});

    				check_outros();
    			}
    		},
    		i: function intro(local) {
    			if (current) return;
    			transition_in(if_block);
    			current = true;
    		},
    		o: function outro(local) {
    			transition_out(if_block);
    			current = false;
    		},
    		d: function destroy(detaching) {
    			if (if_block) if_block.d(detaching);
    			if (detaching) detach_dev(if_block_anchor);
    		}
    	};

    	dispatch_dev("SvelteRegisterBlock", {
    		block,
    		id: create_fragment$O.name,
    		type: "component",
    		source: "",
    		ctx
    	});

    	return block;
    }

    function instance$O($$self, $$props, $$invalidate) {
    	let displayItems;
    	let $urls;
    	validate_store(urls, 'urls');
    	component_subscribe($$self, urls, $$value => $$invalidate(4, $urls = $$value));
    	let { $$slots: slots = {}, $$scope } = $$props;
    	validate_slots('SubNav', slots, []);
    	let { name = "media" } = $$props;
    	let { items = [] } = $$props;
    	let { subpage = false } = $$props;
    	let { progress = false } = $$props;
    	const writable_props = ['name', 'items', 'subpage', 'progress'];

    	Object.keys($$props).forEach(key => {
    		if (!~writable_props.indexOf(key) && key.slice(0, 2) !== '$$' && key !== 'slot') console.warn(`<SubNav> was created with unknown prop '${key}'`);
    	});

    	$$self.$$set = $$props => {
    		if ('name' in $$props) $$invalidate(0, name = $$props.name);
    		if ('items' in $$props) $$invalidate(5, items = $$props.items);
    		if ('subpage' in $$props) $$invalidate(1, subpage = $$props.subpage);
    		if ('progress' in $$props) $$invalidate(2, progress = $$props.progress);
    	};

    	$$self.$capture_state = () => ({
    		urls,
    		SubNavItem,
    		name,
    		items,
    		subpage,
    		progress,
    		displayItems,
    		$urls
    	});

    	$$self.$inject_state = $$props => {
    		if ('name' in $$props) $$invalidate(0, name = $$props.name);
    		if ('items' in $$props) $$invalidate(5, items = $$props.items);
    		if ('subpage' in $$props) $$invalidate(1, subpage = $$props.subpage);
    		if ('progress' in $$props) $$invalidate(2, progress = $$props.progress);
    		if ('displayItems' in $$props) $$invalidate(3, displayItems = $$props.displayItems);
    	};

    	if ($$props && "$$inject" in $$props) {
    		$$self.$inject_state($$props.$$inject);
    	}

    	$$self.$$.update = () => {
    		if ($$self.$$.dirty & /*items*/ 32) {
    			$$invalidate(3, displayItems = items.filter(page => page.title && (!page.hasOwnProperty("enabled") || page.enabled() === true)));
    		}
    	};

    	return [name, subpage, progress, displayItems, $urls, items];
    }

    class SubNav extends SvelteComponentDev {
    	constructor(options) {
    		super(options);

    		init(this, options, instance$O, create_fragment$O, safe_not_equal, {
    			name: 0,
    			items: 5,
    			subpage: 1,
    			progress: 2
    		});

    		dispatch_dev("SvelteRegisterComponent", {
    			component: this,
    			tagName: "SubNav",
    			options,
    			id: create_fragment$O.name
    		});
    	}

    	get name() {
    		throw new Error("<SubNav>: Props cannot be read directly from the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
    	}

    	set name(value) {
    		throw new Error("<SubNav>: Props cannot be set directly on the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
    	}

    	get items() {
    		throw new Error("<SubNav>: Props cannot be read directly from the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
    	}

    	set items(value) {
    		throw new Error("<SubNav>: Props cannot be set directly on the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
    	}

    	get subpage() {
    		throw new Error("<SubNav>: Props cannot be read directly from the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
    	}

    	set subpage(value) {
    		throw new Error("<SubNav>: Props cannot be set directly on the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
    	}

    	get progress() {
    		throw new Error("<SubNav>: Props cannot be read directly from the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
    	}

    	set progress(value) {
    		throw new Error("<SubNav>: Props cannot be set directly on the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
    	}
    }

    /* src/amazon-s3-and-cloudfront/ui/components/SubPages.svelte generated by Svelte v3.49.0 */
    const file$G = "src/amazon-s3-and-cloudfront/ui/components/SubPages.svelte";

    // (9:0) {#if routes}
    function create_if_block$k(ctx) {
    	let div;
    	let router;
    	let t;
    	let div_class_value;
    	let current;

    	router = new Router({
    			props: {
    				routes: /*routes*/ ctx[2],
    				prefix: /*prefix*/ ctx[1]
    			},
    			$$inline: true
    		});

    	router.$on("routeEvent", /*routeEvent_handler*/ ctx[5]);
    	const default_slot_template = /*#slots*/ ctx[4].default;
    	const default_slot = create_slot(default_slot_template, ctx, /*$$scope*/ ctx[3], null);

    	const block = {
    		c: function create() {
    			div = element("div");
    			create_component(router.$$.fragment);
    			t = space();
    			if (default_slot) default_slot.c();
    			attr_dev(div, "class", div_class_value = "" + (/*name*/ ctx[0] + "-page wrapper"));
    			add_location(div, file$G, 9, 1, 152);
    		},
    		m: function mount(target, anchor) {
    			insert_dev(target, div, anchor);
    			mount_component(router, div, null);
    			append_dev(div, t);

    			if (default_slot) {
    				default_slot.m(div, null);
    			}

    			current = true;
    		},
    		p: function update(ctx, dirty) {
    			const router_changes = {};
    			if (dirty & /*routes*/ 4) router_changes.routes = /*routes*/ ctx[2];
    			if (dirty & /*prefix*/ 2) router_changes.prefix = /*prefix*/ ctx[1];
    			router.$set(router_changes);

    			if (default_slot) {
    				if (default_slot.p && (!current || dirty & /*$$scope*/ 8)) {
    					update_slot_base(
    						default_slot,
    						default_slot_template,
    						ctx,
    						/*$$scope*/ ctx[3],
    						!current
    						? get_all_dirty_from_scope(/*$$scope*/ ctx[3])
    						: get_slot_changes(default_slot_template, /*$$scope*/ ctx[3], dirty, null),
    						null
    					);
    				}
    			}

    			if (!current || dirty & /*name*/ 1 && div_class_value !== (div_class_value = "" + (/*name*/ ctx[0] + "-page wrapper"))) {
    				attr_dev(div, "class", div_class_value);
    			}
    		},
    		i: function intro(local) {
    			if (current) return;
    			transition_in(router.$$.fragment, local);
    			transition_in(default_slot, local);
    			current = true;
    		},
    		o: function outro(local) {
    			transition_out(router.$$.fragment, local);
    			transition_out(default_slot, local);
    			current = false;
    		},
    		d: function destroy(detaching) {
    			if (detaching) detach_dev(div);
    			destroy_component(router);
    			if (default_slot) default_slot.d(detaching);
    		}
    	};

    	dispatch_dev("SvelteRegisterBlock", {
    		block,
    		id: create_if_block$k.name,
    		type: "if",
    		source: "(9:0) {#if routes}",
    		ctx
    	});

    	return block;
    }

    function create_fragment$N(ctx) {
    	let if_block_anchor;
    	let current;
    	let if_block = /*routes*/ ctx[2] && create_if_block$k(ctx);

    	const block = {
    		c: function create() {
    			if (if_block) if_block.c();
    			if_block_anchor = empty();
    		},
    		l: function claim(nodes) {
    			throw new Error("options.hydrate only works if the component was compiled with the `hydratable: true` option");
    		},
    		m: function mount(target, anchor) {
    			if (if_block) if_block.m(target, anchor);
    			insert_dev(target, if_block_anchor, anchor);
    			current = true;
    		},
    		p: function update(ctx, [dirty]) {
    			if (/*routes*/ ctx[2]) {
    				if (if_block) {
    					if_block.p(ctx, dirty);

    					if (dirty & /*routes*/ 4) {
    						transition_in(if_block, 1);
    					}
    				} else {
    					if_block = create_if_block$k(ctx);
    					if_block.c();
    					transition_in(if_block, 1);
    					if_block.m(if_block_anchor.parentNode, if_block_anchor);
    				}
    			} else if (if_block) {
    				group_outros();

    				transition_out(if_block, 1, 1, () => {
    					if_block = null;
    				});

    				check_outros();
    			}
    		},
    		i: function intro(local) {
    			if (current) return;
    			transition_in(if_block);
    			current = true;
    		},
    		o: function outro(local) {
    			transition_out(if_block);
    			current = false;
    		},
    		d: function destroy(detaching) {
    			if (if_block) if_block.d(detaching);
    			if (detaching) detach_dev(if_block_anchor);
    		}
    	};

    	dispatch_dev("SvelteRegisterBlock", {
    		block,
    		id: create_fragment$N.name,
    		type: "component",
    		source: "",
    		ctx
    	});

    	return block;
    }

    function instance$N($$self, $$props, $$invalidate) {
    	let { $$slots: slots = {}, $$scope } = $$props;
    	validate_slots('SubPages', slots, ['default']);
    	let { name = "sub" } = $$props;
    	let { prefix = "" } = $$props;
    	let { routes = {} } = $$props;
    	const writable_props = ['name', 'prefix', 'routes'];

    	Object.keys($$props).forEach(key => {
    		if (!~writable_props.indexOf(key) && key.slice(0, 2) !== '$$' && key !== 'slot') console.warn(`<SubPages> was created with unknown prop '${key}'`);
    	});

    	function routeEvent_handler(event) {
    		bubble.call(this, $$self, event);
    	}

    	$$self.$$set = $$props => {
    		if ('name' in $$props) $$invalidate(0, name = $$props.name);
    		if ('prefix' in $$props) $$invalidate(1, prefix = $$props.prefix);
    		if ('routes' in $$props) $$invalidate(2, routes = $$props.routes);
    		if ('$$scope' in $$props) $$invalidate(3, $$scope = $$props.$$scope);
    	};

    	$$self.$capture_state = () => ({ Router, name, prefix, routes });

    	$$self.$inject_state = $$props => {
    		if ('name' in $$props) $$invalidate(0, name = $$props.name);
    		if ('prefix' in $$props) $$invalidate(1, prefix = $$props.prefix);
    		if ('routes' in $$props) $$invalidate(2, routes = $$props.routes);
    	};

    	if ($$props && "$$inject" in $$props) {
    		$$self.$inject_state($$props.$$inject);
    	}

    	return [name, prefix, routes, $$scope, slots, routeEvent_handler];
    }

    class SubPages extends SvelteComponentDev {
    	constructor(options) {
    		super(options);
    		init(this, options, instance$N, create_fragment$N, safe_not_equal, { name: 0, prefix: 1, routes: 2 });

    		dispatch_dev("SvelteRegisterComponent", {
    			component: this,
    			tagName: "SubPages",
    			options,
    			id: create_fragment$N.name
    		});
    	}

    	get name() {
    		throw new Error("<SubPages>: Props cannot be read directly from the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
    	}

    	set name(value) {
    		throw new Error("<SubPages>: Props cannot be set directly on the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
    	}

    	get prefix() {
    		throw new Error("<SubPages>: Props cannot be read directly from the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
    	}

    	set prefix(value) {
    		throw new Error("<SubPages>: Props cannot be set directly on the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
    	}

    	get routes() {
    		throw new Error("<SubPages>: Props cannot be read directly from the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
    	}

    	set routes(value) {
    		throw new Error("<SubPages>: Props cannot be set directly on the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
    	}
    }

    // List of nodes to update
    const nodes = [];

    // Current location
    let location;

    // Function that updates all nodes marking the active ones
    function checkActive(el) {
        const matchesLocation = el.pattern.test(location);
        toggleClasses(el, el.className, matchesLocation);
        toggleClasses(el, el.inactiveClassName, !matchesLocation);
    }

    function toggleClasses(el, className, shouldAdd) {
        (className || '').split(' ').forEach((cls) => {
            if (!cls) {
                return
            }
            // Remove the class firsts
            el.node.classList.remove(cls);

            // If the pattern doesn't match, then set the class
            if (shouldAdd) {
                el.node.classList.add(cls);
            }
        });
    }

    // Listen to changes in the location
    loc.subscribe((value) => {
        // Update the location
        location = value.location + (value.querystring ? '?' + value.querystring : '');

        // Update all nodes
        nodes.map(checkActive);
    });

    /**
     * @typedef {Object} ActiveOptions
     * @property {string|RegExp} [path] - Path expression that makes the link active when matched (must start with '/' or '*'); default is the link's href
     * @property {string} [className] - CSS class to apply to the element when active; default value is "active"
     */

    /**
     * Svelte Action for automatically adding the "active" class to elements (links, or any other DOM element) when the current location matches a certain path.
     * 
     * @param {HTMLElement} node - The target node (automatically set by Svelte)
     * @param {ActiveOptions|string|RegExp} [opts] - Can be an object of type ActiveOptions, or a string (or regular expressions) representing ActiveOptions.path.
     * @returns {{destroy: function(): void}} Destroy function
     */
    function active(node, opts) {
        // Check options
        if (opts && (typeof opts == 'string' || (typeof opts == 'object' && opts instanceof RegExp))) {
            // Interpret strings and regular expressions as opts.path
            opts = {
                path: opts
            };
        }
        else {
            // Ensure opts is a dictionary
            opts = opts || {};
        }

        // Path defaults to link target
        if (!opts.path && node.hasAttribute('href')) {
            opts.path = node.getAttribute('href');
            if (opts.path && opts.path.length > 1 && opts.path.charAt(0) == '#') {
                opts.path = opts.path.substring(1);
            }
        }

        // Default class name
        if (!opts.className) {
            opts.className = 'active';
        }

        // If path is a string, it must start with '/' or '*'
        if (!opts.path || 
            typeof opts.path == 'string' && (opts.path.length < 1 || (opts.path.charAt(0) != '/' && opts.path.charAt(0) != '*'))
        ) {
            throw Error('Invalid value for "path" argument')
        }

        // If path is not a regular expression already, make it
        const {pattern} = typeof opts.path == 'string' ?
            parse(opts.path) :
            {pattern: opts.path};

        // Add the node to the list
        const el = {
            node,
            className: opts.className,
            inactiveClassName: opts.inactiveClassName,
            pattern
        };
        nodes.push(el);

        // Trigger the action right away
        checkActive(el);

        return {
            // When the element is destroyed, remove it from the list
            destroy() {
                nodes.splice(nodes.indexOf(el), 1);
            }
        }
    }

    /* src/amazon-s3-and-cloudfront/ui/components/SubPage.svelte generated by Svelte v3.49.0 */
    const file$F = "src/amazon-s3-and-cloudfront/ui/components/SubPage.svelte";

    function create_fragment$M(ctx) {
    	let div;
    	let active_action;
    	let current;
    	let mounted;
    	let dispose;
    	const default_slot_template = /*#slots*/ ctx[3].default;
    	const default_slot = create_slot(default_slot_template, ctx, /*$$scope*/ ctx[2], null);

    	const block = {
    		c: function create() {
    			div = element("div");
    			if (default_slot) default_slot.c();
    			attr_dev(div, "class", /*name*/ ctx[0]);
    			add_location(div, file$F, 7, 0, 117);
    		},
    		l: function claim(nodes) {
    			throw new Error("options.hydrate only works if the component was compiled with the `hydratable: true` option");
    		},
    		m: function mount(target, anchor) {
    			insert_dev(target, div, anchor);

    			if (default_slot) {
    				default_slot.m(div, null);
    			}

    			current = true;

    			if (!mounted) {
    				dispose = action_destroyer(active_action = active.call(null, div, /*route*/ ctx[1]));
    				mounted = true;
    			}
    		},
    		p: function update(ctx, [dirty]) {
    			if (default_slot) {
    				if (default_slot.p && (!current || dirty & /*$$scope*/ 4)) {
    					update_slot_base(
    						default_slot,
    						default_slot_template,
    						ctx,
    						/*$$scope*/ ctx[2],
    						!current
    						? get_all_dirty_from_scope(/*$$scope*/ ctx[2])
    						: get_slot_changes(default_slot_template, /*$$scope*/ ctx[2], dirty, null),
    						null
    					);
    				}
    			}

    			if (!current || dirty & /*name*/ 1) {
    				attr_dev(div, "class", /*name*/ ctx[0]);
    			}

    			if (active_action && is_function(active_action.update) && dirty & /*route*/ 2) active_action.update.call(null, /*route*/ ctx[1]);
    		},
    		i: function intro(local) {
    			if (current) return;
    			transition_in(default_slot, local);
    			current = true;
    		},
    		o: function outro(local) {
    			transition_out(default_slot, local);
    			current = false;
    		},
    		d: function destroy(detaching) {
    			if (detaching) detach_dev(div);
    			if (default_slot) default_slot.d(detaching);
    			mounted = false;
    			dispose();
    		}
    	};

    	dispatch_dev("SvelteRegisterBlock", {
    		block,
    		id: create_fragment$M.name,
    		type: "component",
    		source: "",
    		ctx
    	});

    	return block;
    }

    function instance$M($$self, $$props, $$invalidate) {
    	let { $$slots: slots = {}, $$scope } = $$props;
    	validate_slots('SubPage', slots, ['default']);
    	let { name = "" } = $$props;
    	let { route = "/" } = $$props;
    	const writable_props = ['name', 'route'];

    	Object.keys($$props).forEach(key => {
    		if (!~writable_props.indexOf(key) && key.slice(0, 2) !== '$$' && key !== 'slot') console.warn(`<SubPage> was created with unknown prop '${key}'`);
    	});

    	$$self.$$set = $$props => {
    		if ('name' in $$props) $$invalidate(0, name = $$props.name);
    		if ('route' in $$props) $$invalidate(1, route = $$props.route);
    		if ('$$scope' in $$props) $$invalidate(2, $$scope = $$props.$$scope);
    	};

    	$$self.$capture_state = () => ({ active, name, route });

    	$$self.$inject_state = $$props => {
    		if ('name' in $$props) $$invalidate(0, name = $$props.name);
    		if ('route' in $$props) $$invalidate(1, route = $$props.route);
    	};

    	if ($$props && "$$inject" in $$props) {
    		$$self.$inject_state($$props.$$inject);
    	}

    	return [name, route, $$scope, slots];
    }

    class SubPage extends SvelteComponentDev {
    	constructor(options) {
    		super(options);
    		init(this, options, instance$M, create_fragment$M, safe_not_equal, { name: 0, route: 1 });

    		dispatch_dev("SvelteRegisterComponent", {
    			component: this,
    			tagName: "SubPage",
    			options,
    			id: create_fragment$M.name
    		});
    	}

    	get name() {
    		throw new Error("<SubPage>: Props cannot be read directly from the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
    	}

    	set name(value) {
    		throw new Error("<SubPage>: Props cannot be set directly on the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
    	}

    	get route() {
    		throw new Error("<SubPage>: Props cannot be read directly from the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
    	}

    	set route(value) {
    		throw new Error("<SubPage>: Props cannot be set directly on the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
    	}
    }

    /* src/amazon-s3-and-cloudfront/ui/components/PanelContainer.svelte generated by Svelte v3.49.0 */

    const file$E = "src/amazon-s3-and-cloudfront/ui/components/PanelContainer.svelte";

    function create_fragment$L(ctx) {
    	let div;
    	let current;
    	const default_slot_template = /*#slots*/ ctx[2].default;
    	const default_slot = create_slot(default_slot_template, ctx, /*$$scope*/ ctx[1], null);

    	const block = {
    		c: function create() {
    			div = element("div");
    			if (default_slot) default_slot.c();
    			attr_dev(div, "class", "panel-container " + /*classes*/ ctx[0]);
    			add_location(div, file$E, 4, 0, 73);
    		},
    		l: function claim(nodes) {
    			throw new Error("options.hydrate only works if the component was compiled with the `hydratable: true` option");
    		},
    		m: function mount(target, anchor) {
    			insert_dev(target, div, anchor);

    			if (default_slot) {
    				default_slot.m(div, null);
    			}

    			current = true;
    		},
    		p: function update(ctx, [dirty]) {
    			if (default_slot) {
    				if (default_slot.p && (!current || dirty & /*$$scope*/ 2)) {
    					update_slot_base(
    						default_slot,
    						default_slot_template,
    						ctx,
    						/*$$scope*/ ctx[1],
    						!current
    						? get_all_dirty_from_scope(/*$$scope*/ ctx[1])
    						: get_slot_changes(default_slot_template, /*$$scope*/ ctx[1], dirty, null),
    						null
    					);
    				}
    			}
    		},
    		i: function intro(local) {
    			if (current) return;
    			transition_in(default_slot, local);
    			current = true;
    		},
    		o: function outro(local) {
    			transition_out(default_slot, local);
    			current = false;
    		},
    		d: function destroy(detaching) {
    			if (detaching) detach_dev(div);
    			if (default_slot) default_slot.d(detaching);
    		}
    	};

    	dispatch_dev("SvelteRegisterBlock", {
    		block,
    		id: create_fragment$L.name,
    		type: "component",
    		source: "",
    		ctx
    	});

    	return block;
    }

    function instance$L($$self, $$props, $$invalidate) {
    	let { $$slots: slots = {}, $$scope } = $$props;
    	validate_slots('PanelContainer', slots, ['default']);
    	const classes = $$props.class ? $$props.class : "";

    	$$self.$$set = $$new_props => {
    		$$invalidate(3, $$props = assign(assign({}, $$props), exclude_internal_props($$new_props)));
    		if ('$$scope' in $$new_props) $$invalidate(1, $$scope = $$new_props.$$scope);
    	};

    	$$self.$capture_state = () => ({ classes });

    	$$self.$inject_state = $$new_props => {
    		$$invalidate(3, $$props = assign(assign({}, $$props), $$new_props));
    	};

    	if ($$props && "$$inject" in $$props) {
    		$$self.$inject_state($$props.$$inject);
    	}

    	$$props = exclude_internal_props($$props);
    	return [classes, $$scope, slots];
    }

    class PanelContainer extends SvelteComponentDev {
    	constructor(options) {
    		super(options);
    		init(this, options, instance$L, create_fragment$L, safe_not_equal, {});

    		dispatch_dev("SvelteRegisterComponent", {
    			component: this,
    			tagName: "PanelContainer",
    			options,
    			id: create_fragment$L.name
    		});
    	}
    }

    /* src/amazon-s3-and-cloudfront/ui/components/PanelRow.svelte generated by Svelte v3.49.0 */

    const file$D = "src/amazon-s3-and-cloudfront/ui/components/PanelRow.svelte";

    // (10:1) {#if gradient}
    function create_if_block$j(ctx) {
    	let div;

    	const block = {
    		c: function create() {
    			div = element("div");
    			attr_dev(div, "class", "gradient svelte-41r5oq");
    			add_location(div, file$D, 10, 2, 238);
    		},
    		m: function mount(target, anchor) {
    			insert_dev(target, div, anchor);
    		},
    		d: function destroy(detaching) {
    			if (detaching) detach_dev(div);
    		}
    	};

    	dispatch_dev("SvelteRegisterBlock", {
    		block,
    		id: create_if_block$j.name,
    		type: "if",
    		source: "(10:1) {#if gradient}",
    		ctx
    	});

    	return block;
    }

    function create_fragment$K(ctx) {
    	let div;
    	let t;
    	let current;
    	let if_block = /*gradient*/ ctx[2] && create_if_block$j(ctx);
    	const default_slot_template = /*#slots*/ ctx[5].default;
    	const default_slot = create_slot(default_slot_template, ctx, /*$$scope*/ ctx[4], null);

    	const block = {
    		c: function create() {
    			div = element("div");
    			if (if_block) if_block.c();
    			t = space();
    			if (default_slot) default_slot.c();
    			attr_dev(div, "class", "panel-row " + /*classes*/ ctx[3] + " svelte-41r5oq");
    			toggle_class(div, "header", /*header*/ ctx[0]);
    			toggle_class(div, "footer", /*footer*/ ctx[1]);
    			add_location(div, file$D, 8, 0, 160);
    		},
    		l: function claim(nodes) {
    			throw new Error("options.hydrate only works if the component was compiled with the `hydratable: true` option");
    		},
    		m: function mount(target, anchor) {
    			insert_dev(target, div, anchor);
    			if (if_block) if_block.m(div, null);
    			append_dev(div, t);

    			if (default_slot) {
    				default_slot.m(div, null);
    			}

    			current = true;
    		},
    		p: function update(ctx, [dirty]) {
    			if (/*gradient*/ ctx[2]) {
    				if (if_block) ; else {
    					if_block = create_if_block$j(ctx);
    					if_block.c();
    					if_block.m(div, t);
    				}
    			} else if (if_block) {
    				if_block.d(1);
    				if_block = null;
    			}

    			if (default_slot) {
    				if (default_slot.p && (!current || dirty & /*$$scope*/ 16)) {
    					update_slot_base(
    						default_slot,
    						default_slot_template,
    						ctx,
    						/*$$scope*/ ctx[4],
    						!current
    						? get_all_dirty_from_scope(/*$$scope*/ ctx[4])
    						: get_slot_changes(default_slot_template, /*$$scope*/ ctx[4], dirty, null),
    						null
    					);
    				}
    			}

    			if (dirty & /*header*/ 1) {
    				toggle_class(div, "header", /*header*/ ctx[0]);
    			}

    			if (dirty & /*footer*/ 2) {
    				toggle_class(div, "footer", /*footer*/ ctx[1]);
    			}
    		},
    		i: function intro(local) {
    			if (current) return;
    			transition_in(default_slot, local);
    			current = true;
    		},
    		o: function outro(local) {
    			transition_out(default_slot, local);
    			current = false;
    		},
    		d: function destroy(detaching) {
    			if (detaching) detach_dev(div);
    			if (if_block) if_block.d();
    			if (default_slot) default_slot.d(detaching);
    		}
    	};

    	dispatch_dev("SvelteRegisterBlock", {
    		block,
    		id: create_fragment$K.name,
    		type: "component",
    		source: "",
    		ctx
    	});

    	return block;
    }

    function instance$K($$self, $$props, $$invalidate) {
    	let { $$slots: slots = {}, $$scope } = $$props;
    	validate_slots('PanelRow', slots, ['default']);
    	const classes = $$props.class ? $$props.class : "";
    	let { header = false } = $$props;
    	let { footer = false } = $$props;
    	let { gradient = false } = $$props;

    	$$self.$$set = $$new_props => {
    		$$invalidate(6, $$props = assign(assign({}, $$props), exclude_internal_props($$new_props)));
    		if ('header' in $$new_props) $$invalidate(0, header = $$new_props.header);
    		if ('footer' in $$new_props) $$invalidate(1, footer = $$new_props.footer);
    		if ('gradient' in $$new_props) $$invalidate(2, gradient = $$new_props.gradient);
    		if ('$$scope' in $$new_props) $$invalidate(4, $$scope = $$new_props.$$scope);
    	};

    	$$self.$capture_state = () => ({ classes, header, footer, gradient });

    	$$self.$inject_state = $$new_props => {
    		$$invalidate(6, $$props = assign(assign({}, $$props), $$new_props));
    		if ('header' in $$props) $$invalidate(0, header = $$new_props.header);
    		if ('footer' in $$props) $$invalidate(1, footer = $$new_props.footer);
    		if ('gradient' in $$props) $$invalidate(2, gradient = $$new_props.gradient);
    	};

    	if ($$props && "$$inject" in $$props) {
    		$$self.$inject_state($$props.$$inject);
    	}

    	$$props = exclude_internal_props($$props);
    	return [header, footer, gradient, classes, $$scope, slots];
    }

    class PanelRow extends SvelteComponentDev {
    	constructor(options) {
    		super(options);
    		init(this, options, instance$K, create_fragment$K, safe_not_equal, { header: 0, footer: 1, gradient: 2 });

    		dispatch_dev("SvelteRegisterComponent", {
    			component: this,
    			tagName: "PanelRow",
    			options,
    			id: create_fragment$K.name
    		});
    	}

    	get header() {
    		throw new Error("<PanelRow>: Props cannot be read directly from the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
    	}

    	set header(value) {
    		throw new Error("<PanelRow>: Props cannot be set directly on the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
    	}

    	get footer() {
    		throw new Error("<PanelRow>: Props cannot be read directly from the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
    	}

    	set footer(value) {
    		throw new Error("<PanelRow>: Props cannot be set directly on the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
    	}

    	get gradient() {
    		throw new Error("<PanelRow>: Props cannot be read directly from the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
    	}

    	set gradient(value) {
    		throw new Error("<PanelRow>: Props cannot be set directly on the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
    	}
    }

    /* src/amazon-s3-and-cloudfront/ui/components/DefinedInWPConfig.svelte generated by Svelte v3.49.0 */
    const file$C = "src/amazon-s3-and-cloudfront/ui/components/DefinedInWPConfig.svelte";

    // (7:0) {#if defined}
    function create_if_block$i(ctx) {
    	let p;
    	let t_value = /*$strings*/ ctx[1].defined_in_wp_config + "";
    	let t;

    	const block = {
    		c: function create() {
    			p = element("p");
    			t = text(t_value);
    			attr_dev(p, "class", "wp-config");
    			add_location(p, file$C, 7, 1, 104);
    		},
    		m: function mount(target, anchor) {
    			insert_dev(target, p, anchor);
    			append_dev(p, t);
    		},
    		p: function update(ctx, dirty) {
    			if (dirty & /*$strings*/ 2 && t_value !== (t_value = /*$strings*/ ctx[1].defined_in_wp_config + "")) set_data_dev(t, t_value);
    		},
    		d: function destroy(detaching) {
    			if (detaching) detach_dev(p);
    		}
    	};

    	dispatch_dev("SvelteRegisterBlock", {
    		block,
    		id: create_if_block$i.name,
    		type: "if",
    		source: "(7:0) {#if defined}",
    		ctx
    	});

    	return block;
    }

    function create_fragment$J(ctx) {
    	let if_block_anchor;
    	let if_block = /*defined*/ ctx[0] && create_if_block$i(ctx);

    	const block = {
    		c: function create() {
    			if (if_block) if_block.c();
    			if_block_anchor = empty();
    		},
    		l: function claim(nodes) {
    			throw new Error("options.hydrate only works if the component was compiled with the `hydratable: true` option");
    		},
    		m: function mount(target, anchor) {
    			if (if_block) if_block.m(target, anchor);
    			insert_dev(target, if_block_anchor, anchor);
    		},
    		p: function update(ctx, [dirty]) {
    			if (/*defined*/ ctx[0]) {
    				if (if_block) {
    					if_block.p(ctx, dirty);
    				} else {
    					if_block = create_if_block$i(ctx);
    					if_block.c();
    					if_block.m(if_block_anchor.parentNode, if_block_anchor);
    				}
    			} else if (if_block) {
    				if_block.d(1);
    				if_block = null;
    			}
    		},
    		i: noop,
    		o: noop,
    		d: function destroy(detaching) {
    			if (if_block) if_block.d(detaching);
    			if (detaching) detach_dev(if_block_anchor);
    		}
    	};

    	dispatch_dev("SvelteRegisterBlock", {
    		block,
    		id: create_fragment$J.name,
    		type: "component",
    		source: "",
    		ctx
    	});

    	return block;
    }

    function instance$J($$self, $$props, $$invalidate) {
    	let $strings;
    	validate_store(strings, 'strings');
    	component_subscribe($$self, strings, $$value => $$invalidate(1, $strings = $$value));
    	let { $$slots: slots = {}, $$scope } = $$props;
    	validate_slots('DefinedInWPConfig', slots, []);
    	let { defined = false } = $$props;
    	const writable_props = ['defined'];

    	Object.keys($$props).forEach(key => {
    		if (!~writable_props.indexOf(key) && key.slice(0, 2) !== '$$' && key !== 'slot') console.warn(`<DefinedInWPConfig> was created with unknown prop '${key}'`);
    	});

    	$$self.$$set = $$props => {
    		if ('defined' in $$props) $$invalidate(0, defined = $$props.defined);
    	};

    	$$self.$capture_state = () => ({ strings, defined, $strings });

    	$$self.$inject_state = $$props => {
    		if ('defined' in $$props) $$invalidate(0, defined = $$props.defined);
    	};

    	if ($$props && "$$inject" in $$props) {
    		$$self.$inject_state($$props.$$inject);
    	}

    	return [defined, $strings];
    }

    class DefinedInWPConfig extends SvelteComponentDev {
    	constructor(options) {
    		super(options);
    		init(this, options, instance$J, create_fragment$J, safe_not_equal, { defined: 0 });

    		dispatch_dev("SvelteRegisterComponent", {
    			component: this,
    			tagName: "DefinedInWPConfig",
    			options,
    			id: create_fragment$J.name
    		});
    	}

    	get defined() {
    		throw new Error("<DefinedInWPConfig>: Props cannot be read directly from the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
    	}

    	set defined(value) {
    		throw new Error("<DefinedInWPConfig>: Props cannot be set directly on the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
    	}
    }

    /* src/amazon-s3-and-cloudfront/ui/components/ToggleSwitch.svelte generated by Svelte v3.49.0 */

    const file$B = "src/amazon-s3-and-cloudfront/ui/components/ToggleSwitch.svelte";

    function create_fragment$I(ctx) {
    	let div;
    	let input;
    	let t;
    	let label;
    	let current;
    	let mounted;
    	let dispose;
    	const default_slot_template = /*#slots*/ ctx[4].default;
    	const default_slot = create_slot(default_slot_template, ctx, /*$$scope*/ ctx[3], null);

    	const block = {
    		c: function create() {
    			div = element("div");
    			input = element("input");
    			t = space();
    			label = element("label");
    			if (default_slot) default_slot.c();
    			attr_dev(input, "type", "checkbox");
    			attr_dev(input, "id", /*name*/ ctx[1]);
    			input.disabled = /*disabled*/ ctx[2];
    			add_location(input, file$B, 7, 1, 155);
    			attr_dev(label, "class", "toggle-label");
    			attr_dev(label, "for", /*name*/ ctx[1]);
    			add_location(label, file$B, 13, 1, 235);
    			attr_dev(div, "class", "toggle-switch");
    			toggle_class(div, "locked", /*disabled*/ ctx[2]);
    			add_location(div, file$B, 6, 0, 102);
    		},
    		l: function claim(nodes) {
    			throw new Error("options.hydrate only works if the component was compiled with the `hydratable: true` option");
    		},
    		m: function mount(target, anchor) {
    			insert_dev(target, div, anchor);
    			append_dev(div, input);
    			input.checked = /*checked*/ ctx[0];
    			append_dev(div, t);
    			append_dev(div, label);

    			if (default_slot) {
    				default_slot.m(label, null);
    			}

    			current = true;

    			if (!mounted) {
    				dispose = listen_dev(input, "change", /*input_change_handler*/ ctx[5]);
    				mounted = true;
    			}
    		},
    		p: function update(ctx, [dirty]) {
    			if (!current || dirty & /*name*/ 2) {
    				attr_dev(input, "id", /*name*/ ctx[1]);
    			}

    			if (!current || dirty & /*disabled*/ 4) {
    				prop_dev(input, "disabled", /*disabled*/ ctx[2]);
    			}

    			if (dirty & /*checked*/ 1) {
    				input.checked = /*checked*/ ctx[0];
    			}

    			if (default_slot) {
    				if (default_slot.p && (!current || dirty & /*$$scope*/ 8)) {
    					update_slot_base(
    						default_slot,
    						default_slot_template,
    						ctx,
    						/*$$scope*/ ctx[3],
    						!current
    						? get_all_dirty_from_scope(/*$$scope*/ ctx[3])
    						: get_slot_changes(default_slot_template, /*$$scope*/ ctx[3], dirty, null),
    						null
    					);
    				}
    			}

    			if (!current || dirty & /*name*/ 2) {
    				attr_dev(label, "for", /*name*/ ctx[1]);
    			}

    			if (dirty & /*disabled*/ 4) {
    				toggle_class(div, "locked", /*disabled*/ ctx[2]);
    			}
    		},
    		i: function intro(local) {
    			if (current) return;
    			transition_in(default_slot, local);
    			current = true;
    		},
    		o: function outro(local) {
    			transition_out(default_slot, local);
    			current = false;
    		},
    		d: function destroy(detaching) {
    			if (detaching) detach_dev(div);
    			if (default_slot) default_slot.d(detaching);
    			mounted = false;
    			dispose();
    		}
    	};

    	dispatch_dev("SvelteRegisterBlock", {
    		block,
    		id: create_fragment$I.name,
    		type: "component",
    		source: "",
    		ctx
    	});

    	return block;
    }

    function instance$I($$self, $$props, $$invalidate) {
    	let { $$slots: slots = {}, $$scope } = $$props;
    	validate_slots('ToggleSwitch', slots, ['default']);
    	let { name = "" } = $$props;
    	let { checked = false } = $$props;
    	let { disabled = false } = $$props;
    	const writable_props = ['name', 'checked', 'disabled'];

    	Object.keys($$props).forEach(key => {
    		if (!~writable_props.indexOf(key) && key.slice(0, 2) !== '$$' && key !== 'slot') console.warn(`<ToggleSwitch> was created with unknown prop '${key}'`);
    	});

    	function input_change_handler() {
    		checked = this.checked;
    		$$invalidate(0, checked);
    	}

    	$$self.$$set = $$props => {
    		if ('name' in $$props) $$invalidate(1, name = $$props.name);
    		if ('checked' in $$props) $$invalidate(0, checked = $$props.checked);
    		if ('disabled' in $$props) $$invalidate(2, disabled = $$props.disabled);
    		if ('$$scope' in $$props) $$invalidate(3, $$scope = $$props.$$scope);
    	};

    	$$self.$capture_state = () => ({ name, checked, disabled });

    	$$self.$inject_state = $$props => {
    		if ('name' in $$props) $$invalidate(1, name = $$props.name);
    		if ('checked' in $$props) $$invalidate(0, checked = $$props.checked);
    		if ('disabled' in $$props) $$invalidate(2, disabled = $$props.disabled);
    	};

    	if ($$props && "$$inject" in $$props) {
    		$$self.$inject_state($$props.$$inject);
    	}

    	return [checked, name, disabled, $$scope, slots, input_change_handler];
    }

    class ToggleSwitch extends SvelteComponentDev {
    	constructor(options) {
    		super(options);
    		init(this, options, instance$I, create_fragment$I, safe_not_equal, { name: 1, checked: 0, disabled: 2 });

    		dispatch_dev("SvelteRegisterComponent", {
    			component: this,
    			tagName: "ToggleSwitch",
    			options,
    			id: create_fragment$I.name
    		});
    	}

    	get name() {
    		throw new Error("<ToggleSwitch>: Props cannot be read directly from the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
    	}

    	set name(value) {
    		throw new Error("<ToggleSwitch>: Props cannot be set directly on the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
    	}

    	get checked() {
    		throw new Error("<ToggleSwitch>: Props cannot be read directly from the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
    	}

    	set checked(value) {
    		throw new Error("<ToggleSwitch>: Props cannot be set directly on the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
    	}

    	get disabled() {
    		throw new Error("<ToggleSwitch>: Props cannot be read directly from the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
    	}

    	set disabled(value) {
    		throw new Error("<ToggleSwitch>: Props cannot be set directly on the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
    	}
    }

    /* src/amazon-s3-and-cloudfront/ui/components/HelpButton.svelte generated by Svelte v3.49.0 */
    const file$A = "src/amazon-s3-and-cloudfront/ui/components/HelpButton.svelte";

    // (13:0) {#if url}
    function create_if_block$h(ctx) {
    	let a;
    	let img;
    	let img_src_value;

    	const block = {
    		c: function create() {
    			a = element("a");
    			img = element("img");
    			attr_dev(img, "class", "icon help");
    			if (!src_url_equal(img.src, img_src_value = /*$urls*/ ctx[2].assets + 'img/icon/help.svg')) attr_dev(img, "src", img_src_value);
    			attr_dev(img, "alt", /*alt*/ ctx[3]);
    			add_location(img, file$A, 14, 2, 603);
    			attr_dev(a, "href", /*url*/ ctx[1]);
    			attr_dev(a, "title", /*title*/ ctx[4]);
    			attr_dev(a, "class", "help");
    			attr_dev(a, "target", "_blank");
    			attr_dev(a, "data-setting-key", /*key*/ ctx[0]);
    			add_location(a, file$A, 13, 1, 526);
    		},
    		m: function mount(target, anchor) {
    			insert_dev(target, a, anchor);
    			append_dev(a, img);
    		},
    		p: function update(ctx, dirty) {
    			if (dirty & /*$urls*/ 4 && !src_url_equal(img.src, img_src_value = /*$urls*/ ctx[2].assets + 'img/icon/help.svg')) {
    				attr_dev(img, "src", img_src_value);
    			}

    			if (dirty & /*url*/ 2) {
    				attr_dev(a, "href", /*url*/ ctx[1]);
    			}

    			if (dirty & /*key*/ 1) {
    				attr_dev(a, "data-setting-key", /*key*/ ctx[0]);
    			}
    		},
    		d: function destroy(detaching) {
    			if (detaching) detach_dev(a);
    		}
    	};

    	dispatch_dev("SvelteRegisterBlock", {
    		block,
    		id: create_if_block$h.name,
    		type: "if",
    		source: "(13:0) {#if url}",
    		ctx
    	});

    	return block;
    }

    function create_fragment$H(ctx) {
    	let if_block_anchor;
    	let if_block = /*url*/ ctx[1] && create_if_block$h(ctx);

    	const block = {
    		c: function create() {
    			if (if_block) if_block.c();
    			if_block_anchor = empty();
    		},
    		l: function claim(nodes) {
    			throw new Error("options.hydrate only works if the component was compiled with the `hydratable: true` option");
    		},
    		m: function mount(target, anchor) {
    			if (if_block) if_block.m(target, anchor);
    			insert_dev(target, if_block_anchor, anchor);
    		},
    		p: function update(ctx, [dirty]) {
    			if (/*url*/ ctx[1]) {
    				if (if_block) {
    					if_block.p(ctx, dirty);
    				} else {
    					if_block = create_if_block$h(ctx);
    					if_block.c();
    					if_block.m(if_block_anchor.parentNode, if_block_anchor);
    				}
    			} else if (if_block) {
    				if_block.d(1);
    				if_block = null;
    			}
    		},
    		i: noop,
    		o: noop,
    		d: function destroy(detaching) {
    			if (if_block) if_block.d(detaching);
    			if (detaching) detach_dev(if_block_anchor);
    		}
    	};

    	dispatch_dev("SvelteRegisterBlock", {
    		block,
    		id: create_fragment$H.name,
    		type: "component",
    		source: "",
    		ctx
    	});

    	return block;
    }

    function instance$H($$self, $$props, $$invalidate) {
    	let $strings;
    	let $docs;
    	let $urls;
    	validate_store(strings, 'strings');
    	component_subscribe($$self, strings, $$value => $$invalidate(6, $strings = $$value));
    	validate_store(docs, 'docs');
    	component_subscribe($$self, docs, $$value => $$invalidate(7, $docs = $$value));
    	validate_store(urls, 'urls');
    	component_subscribe($$self, urls, $$value => $$invalidate(2, $urls = $$value));
    	let { $$slots: slots = {}, $$scope } = $$props;
    	validate_slots('HelpButton', slots, []);
    	let { key = "" } = $$props;

    	let { url = key && $docs.hasOwnProperty(key) && $docs[key].hasOwnProperty("url")
    	? $docs[key].url
    	: "" } = $$props;

    	let { desc = "" } = $$props;

    	// If desc supplied, use it, otherwise try and get via docs store or fall back to default help description.
    	let alt = desc.length
    	? desc
    	: key && $docs.hasOwnProperty(key) && $docs[key].hasOwnProperty("desc")
    		? $docs[key].desc
    		: $strings.help_desc;

    	let title = alt;
    	const writable_props = ['key', 'url', 'desc'];

    	Object.keys($$props).forEach(key => {
    		if (!~writable_props.indexOf(key) && key.slice(0, 2) !== '$$' && key !== 'slot') console.warn(`<HelpButton> was created with unknown prop '${key}'`);
    	});

    	$$self.$$set = $$props => {
    		if ('key' in $$props) $$invalidate(0, key = $$props.key);
    		if ('url' in $$props) $$invalidate(1, url = $$props.url);
    		if ('desc' in $$props) $$invalidate(5, desc = $$props.desc);
    	};

    	$$self.$capture_state = () => ({
    		strings,
    		urls,
    		docs,
    		key,
    		url,
    		desc,
    		alt,
    		title,
    		$strings,
    		$docs,
    		$urls
    	});

    	$$self.$inject_state = $$props => {
    		if ('key' in $$props) $$invalidate(0, key = $$props.key);
    		if ('url' in $$props) $$invalidate(1, url = $$props.url);
    		if ('desc' in $$props) $$invalidate(5, desc = $$props.desc);
    		if ('alt' in $$props) $$invalidate(3, alt = $$props.alt);
    		if ('title' in $$props) $$invalidate(4, title = $$props.title);
    	};

    	if ($$props && "$$inject" in $$props) {
    		$$self.$inject_state($$props.$$inject);
    	}

    	return [key, url, $urls, alt, title, desc];
    }

    class HelpButton extends SvelteComponentDev {
    	constructor(options) {
    		super(options);
    		init(this, options, instance$H, create_fragment$H, safe_not_equal, { key: 0, url: 1, desc: 5 });

    		dispatch_dev("SvelteRegisterComponent", {
    			component: this,
    			tagName: "HelpButton",
    			options,
    			id: create_fragment$H.name
    		});
    	}

    	get key() {
    		throw new Error("<HelpButton>: Props cannot be read directly from the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
    	}

    	set key(value) {
    		throw new Error("<HelpButton>: Props cannot be set directly on the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
    	}

    	get url() {
    		throw new Error("<HelpButton>: Props cannot be read directly from the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
    	}

    	set url(value) {
    		throw new Error("<HelpButton>: Props cannot be set directly on the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
    	}

    	get desc() {
    		throw new Error("<HelpButton>: Props cannot be read directly from the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
    	}

    	set desc(value) {
    		throw new Error("<HelpButton>: Props cannot be set directly on the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
    	}
    }

    /* src/amazon-s3-and-cloudfront/ui/components/Panel.svelte generated by Svelte v3.49.0 */
    const file$z = "src/amazon-s3-and-cloudfront/ui/components/Panel.svelte";

    // (50:1) {#if !multi && heading}
    function create_if_block_5$3(ctx) {
    	let div;
    	let h3;
    	let t0;
    	let t1;
    	let current_block_type_index;
    	let if_block;
    	let t2;
    	let definedinwpconfig;
    	let current;
    	const if_block_creators = [create_if_block_6$3, create_if_block_7$3];
    	const if_blocks = [];

    	function select_block_type(ctx, dirty) {
    		if (/*helpURL*/ ctx[7]) return 0;
    		if (/*helpKey*/ ctx[6]) return 1;
    		return -1;
    	}

    	if (~(current_block_type_index = select_block_type(ctx))) {
    		if_block = if_blocks[current_block_type_index] = if_block_creators[current_block_type_index](ctx);
    	}

    	definedinwpconfig = new DefinedInWPConfig({
    			props: { defined: /*defined*/ ctx[3] },
    			$$inline: true
    		});

    	const block = {
    		c: function create() {
    			div = element("div");
    			h3 = element("h3");
    			t0 = text(/*heading*/ ctx[2]);
    			t1 = space();
    			if (if_block) if_block.c();
    			t2 = space();
    			create_component(definedinwpconfig.$$.fragment);
    			add_location(h3, file$z, 51, 3, 1633);
    			attr_dev(div, "class", "heading");
    			add_location(div, file$z, 50, 2, 1608);
    		},
    		m: function mount(target, anchor) {
    			insert_dev(target, div, anchor);
    			append_dev(div, h3);
    			append_dev(h3, t0);
    			append_dev(div, t1);

    			if (~current_block_type_index) {
    				if_blocks[current_block_type_index].m(div, null);
    			}

    			append_dev(div, t2);
    			mount_component(definedinwpconfig, div, null);
    			current = true;
    		},
    		p: function update(ctx, dirty) {
    			if (!current || dirty & /*heading*/ 4) set_data_dev(t0, /*heading*/ ctx[2]);
    			let previous_block_index = current_block_type_index;
    			current_block_type_index = select_block_type(ctx);

    			if (current_block_type_index === previous_block_index) {
    				if (~current_block_type_index) {
    					if_blocks[current_block_type_index].p(ctx, dirty);
    				}
    			} else {
    				if (if_block) {
    					group_outros();

    					transition_out(if_blocks[previous_block_index], 1, 1, () => {
    						if_blocks[previous_block_index] = null;
    					});

    					check_outros();
    				}

    				if (~current_block_type_index) {
    					if_block = if_blocks[current_block_type_index];

    					if (!if_block) {
    						if_block = if_blocks[current_block_type_index] = if_block_creators[current_block_type_index](ctx);
    						if_block.c();
    					} else {
    						if_block.p(ctx, dirty);
    					}

    					transition_in(if_block, 1);
    					if_block.m(div, t2);
    				} else {
    					if_block = null;
    				}
    			}

    			const definedinwpconfig_changes = {};
    			if (dirty & /*defined*/ 8) definedinwpconfig_changes.defined = /*defined*/ ctx[3];
    			definedinwpconfig.$set(definedinwpconfig_changes);
    		},
    		i: function intro(local) {
    			if (current) return;
    			transition_in(if_block);
    			transition_in(definedinwpconfig.$$.fragment, local);
    			current = true;
    		},
    		o: function outro(local) {
    			transition_out(if_block);
    			transition_out(definedinwpconfig.$$.fragment, local);
    			current = false;
    		},
    		d: function destroy(detaching) {
    			if (detaching) detach_dev(div);

    			if (~current_block_type_index) {
    				if_blocks[current_block_type_index].d();
    			}

    			destroy_component(definedinwpconfig);
    		}
    	};

    	dispatch_dev("SvelteRegisterBlock", {
    		block,
    		id: create_if_block_5$3.name,
    		type: "if",
    		source: "(50:1) {#if !multi && heading}",
    		ctx
    	});

    	return block;
    }

    // (55:21) 
    function create_if_block_7$3(ctx) {
    	let helpbutton;
    	let current;

    	helpbutton = new HelpButton({
    			props: {
    				key: /*helpKey*/ ctx[6],
    				desc: /*helpDesc*/ ctx[8]
    			},
    			$$inline: true
    		});

    	const block = {
    		c: function create() {
    			create_component(helpbutton.$$.fragment);
    		},
    		m: function mount(target, anchor) {
    			mount_component(helpbutton, target, anchor);
    			current = true;
    		},
    		p: function update(ctx, dirty) {
    			const helpbutton_changes = {};
    			if (dirty & /*helpKey*/ 64) helpbutton_changes.key = /*helpKey*/ ctx[6];
    			if (dirty & /*helpDesc*/ 256) helpbutton_changes.desc = /*helpDesc*/ ctx[8];
    			helpbutton.$set(helpbutton_changes);
    		},
    		i: function intro(local) {
    			if (current) return;
    			transition_in(helpbutton.$$.fragment, local);
    			current = true;
    		},
    		o: function outro(local) {
    			transition_out(helpbutton.$$.fragment, local);
    			current = false;
    		},
    		d: function destroy(detaching) {
    			destroy_component(helpbutton, detaching);
    		}
    	};

    	dispatch_dev("SvelteRegisterBlock", {
    		block,
    		id: create_if_block_7$3.name,
    		type: "if",
    		source: "(55:21) ",
    		ctx
    	});

    	return block;
    }

    // (53:3) {#if helpURL}
    function create_if_block_6$3(ctx) {
    	let helpbutton;
    	let current;

    	helpbutton = new HelpButton({
    			props: {
    				url: /*helpURL*/ ctx[7],
    				desc: /*helpDesc*/ ctx[8]
    			},
    			$$inline: true
    		});

    	const block = {
    		c: function create() {
    			create_component(helpbutton.$$.fragment);
    		},
    		m: function mount(target, anchor) {
    			mount_component(helpbutton, target, anchor);
    			current = true;
    		},
    		p: function update(ctx, dirty) {
    			const helpbutton_changes = {};
    			if (dirty & /*helpURL*/ 128) helpbutton_changes.url = /*helpURL*/ ctx[7];
    			if (dirty & /*helpDesc*/ 256) helpbutton_changes.desc = /*helpDesc*/ ctx[8];
    			helpbutton.$set(helpbutton_changes);
    		},
    		i: function intro(local) {
    			if (current) return;
    			transition_in(helpbutton.$$.fragment, local);
    			current = true;
    		},
    		o: function outro(local) {
    			transition_out(helpbutton.$$.fragment, local);
    			current = false;
    		},
    		d: function destroy(detaching) {
    			destroy_component(helpbutton, detaching);
    		}
    	};

    	dispatch_dev("SvelteRegisterBlock", {
    		block,
    		id: create_if_block_6$3.name,
    		type: "if",
    		source: "(53:3) {#if helpURL}",
    		ctx
    	});

    	return block;
    }

    // (62:2) {#if multi && heading}
    function create_if_block$g(ctx) {
    	let panelrow;
    	let current;

    	panelrow = new PanelRow({
    			props: {
    				header: true,
    				$$slots: { default: [create_default_slot_1$b] },
    				$$scope: { ctx }
    			},
    			$$inline: true
    		});

    	const block = {
    		c: function create() {
    			create_component(panelrow.$$.fragment);
    		},
    		m: function mount(target, anchor) {
    			mount_component(panelrow, target, anchor);
    			current = true;
    		},
    		p: function update(ctx, dirty) {
    			const panelrow_changes = {};

    			if (dirty & /*$$scope, helpURL, helpDesc, helpKey, storageProvider, defined, toggleDisabled, heading, toggleName, toggle*/ 529389) {
    				panelrow_changes.$$scope = { dirty, ctx };
    			}

    			panelrow.$set(panelrow_changes);
    		},
    		i: function intro(local) {
    			if (current) return;
    			transition_in(panelrow.$$.fragment, local);
    			current = true;
    		},
    		o: function outro(local) {
    			transition_out(panelrow.$$.fragment, local);
    			current = false;
    		},
    		d: function destroy(detaching) {
    			destroy_component(panelrow, detaching);
    		}
    	};

    	dispatch_dev("SvelteRegisterBlock", {
    		block,
    		id: create_if_block$g.name,
    		type: "if",
    		source: "(62:2) {#if multi && heading}",
    		ctx
    	});

    	return block;
    }

    // (69:4) {:else}
    function create_else_block$3(ctx) {
    	let h3;
    	let t;

    	const block = {
    		c: function create() {
    			h3 = element("h3");
    			t = text(/*heading*/ ctx[2]);
    			add_location(h3, file$z, 69, 5, 2179);
    		},
    		m: function mount(target, anchor) {
    			insert_dev(target, h3, anchor);
    			append_dev(h3, t);
    		},
    		p: function update(ctx, dirty) {
    			if (dirty & /*heading*/ 4) set_data_dev(t, /*heading*/ ctx[2]);
    		},
    		i: noop,
    		o: noop,
    		d: function destroy(detaching) {
    			if (detaching) detach_dev(h3);
    		}
    	};

    	dispatch_dev("SvelteRegisterBlock", {
    		block,
    		id: create_else_block$3.name,
    		type: "else",
    		source: "(69:4) {:else}",
    		ctx
    	});

    	return block;
    }

    // (64:4) {#if toggleName}
    function create_if_block_4$4(ctx) {
    	let toggleswitch;
    	let updating_checked;
    	let t0;
    	let h3;
    	let t1;
    	let current;
    	let mounted;
    	let dispose;

    	function toggleswitch_checked_binding(value) {
    		/*toggleswitch_checked_binding*/ ctx[18](value);
    	}

    	let toggleswitch_props = {
    		name: /*toggleName*/ ctx[5],
    		disabled: /*toggleDisabled*/ ctx[12],
    		$$slots: { default: [create_default_slot_2$7] },
    		$$scope: { ctx }
    	};

    	if (/*toggle*/ ctx[0] !== void 0) {
    		toggleswitch_props.checked = /*toggle*/ ctx[0];
    	}

    	toggleswitch = new ToggleSwitch({
    			props: toggleswitch_props,
    			$$inline: true
    		});

    	binding_callbacks.push(() => bind(toggleswitch, 'checked', toggleswitch_checked_binding));

    	const block = {
    		c: function create() {
    			create_component(toggleswitch.$$.fragment);
    			t0 = space();
    			h3 = element("h3");
    			t1 = text(/*heading*/ ctx[2]);
    			attr_dev(h3, "class", "toggler svelte-k1tgof");
    			toggle_class(h3, "toggleDisabled", /*toggleDisabled*/ ctx[12]);
    			add_location(h3, file$z, 67, 5, 2075);
    		},
    		m: function mount(target, anchor) {
    			mount_component(toggleswitch, target, anchor);
    			insert_dev(target, t0, anchor);
    			insert_dev(target, h3, anchor);
    			append_dev(h3, t1);
    			current = true;

    			if (!mounted) {
    				dispose = listen_dev(h3, "click", /*headingClickHandler*/ ctx[14], false, false, false);
    				mounted = true;
    			}
    		},
    		p: function update(ctx, dirty) {
    			const toggleswitch_changes = {};
    			if (dirty & /*toggleName*/ 32) toggleswitch_changes.name = /*toggleName*/ ctx[5];
    			if (dirty & /*toggleDisabled*/ 4096) toggleswitch_changes.disabled = /*toggleDisabled*/ ctx[12];

    			if (dirty & /*$$scope, heading*/ 524292) {
    				toggleswitch_changes.$$scope = { dirty, ctx };
    			}

    			if (!updating_checked && dirty & /*toggle*/ 1) {
    				updating_checked = true;
    				toggleswitch_changes.checked = /*toggle*/ ctx[0];
    				add_flush_callback(() => updating_checked = false);
    			}

    			toggleswitch.$set(toggleswitch_changes);
    			if (!current || dirty & /*heading*/ 4) set_data_dev(t1, /*heading*/ ctx[2]);

    			if (dirty & /*toggleDisabled*/ 4096) {
    				toggle_class(h3, "toggleDisabled", /*toggleDisabled*/ ctx[12]);
    			}
    		},
    		i: function intro(local) {
    			if (current) return;
    			transition_in(toggleswitch.$$.fragment, local);
    			current = true;
    		},
    		o: function outro(local) {
    			transition_out(toggleswitch.$$.fragment, local);
    			current = false;
    		},
    		d: function destroy(detaching) {
    			destroy_component(toggleswitch, detaching);
    			if (detaching) detach_dev(t0);
    			if (detaching) detach_dev(h3);
    			mounted = false;
    			dispose();
    		}
    	};

    	dispatch_dev("SvelteRegisterBlock", {
    		block,
    		id: create_if_block_4$4.name,
    		type: "if",
    		source: "(64:4) {#if toggleName}",
    		ctx
    	});

    	return block;
    }

    // (65:5) <ToggleSwitch name={toggleName} bind:checked={toggle} disabled={toggleDisabled}>
    function create_default_slot_2$7(ctx) {
    	let t;

    	const block = {
    		c: function create() {
    			t = text(/*heading*/ ctx[2]);
    		},
    		m: function mount(target, anchor) {
    			insert_dev(target, t, anchor);
    		},
    		p: function update(ctx, dirty) {
    			if (dirty & /*heading*/ 4) set_data_dev(t, /*heading*/ ctx[2]);
    		},
    		d: function destroy(detaching) {
    			if (detaching) detach_dev(t);
    		}
    	};

    	dispatch_dev("SvelteRegisterBlock", {
    		block,
    		id: create_default_slot_2$7.name,
    		type: "slot",
    		source: "(65:5) <ToggleSwitch name={toggleName} bind:checked={toggle} disabled={toggleDisabled}>",
    		ctx
    	});

    	return block;
    }

    // (73:4) {#if storageProvider}
    function create_if_block_3$4(ctx) {
    	let div;
    	let a;
    	let img;
    	let img_src_value;
    	let img_alt_value;
    	let t0;
    	let t1_value = /*storageProvider*/ ctx[9].provider_service_name + "";
    	let t1;
    	let mounted;
    	let dispose;

    	const block = {
    		c: function create() {
    			div = element("div");
    			a = element("a");
    			img = element("img");
    			t0 = space();
    			t1 = text(t1_value);
    			if (!src_url_equal(img.src, img_src_value = /*storageProvider*/ ctx[9].link_icon)) attr_dev(img, "src", img_src_value);
    			attr_dev(img, "alt", img_alt_value = /*storageProvider*/ ctx[9].icon_desc);
    			add_location(img, file$z, 75, 7, 2361);
    			attr_dev(a, "href", "/storage/provider");
    			attr_dev(a, "class", "link");
    			add_location(a, file$z, 74, 6, 2303);
    			attr_dev(div, "class", "provider");
    			add_location(div, file$z, 73, 5, 2274);
    		},
    		m: function mount(target, anchor) {
    			insert_dev(target, div, anchor);
    			append_dev(div, a);
    			append_dev(a, img);
    			append_dev(a, t0);
    			append_dev(a, t1);

    			if (!mounted) {
    				dispose = action_destroyer(link.call(null, a));
    				mounted = true;
    			}
    		},
    		p: function update(ctx, dirty) {
    			if (dirty & /*storageProvider*/ 512 && !src_url_equal(img.src, img_src_value = /*storageProvider*/ ctx[9].link_icon)) {
    				attr_dev(img, "src", img_src_value);
    			}

    			if (dirty & /*storageProvider*/ 512 && img_alt_value !== (img_alt_value = /*storageProvider*/ ctx[9].icon_desc)) {
    				attr_dev(img, "alt", img_alt_value);
    			}

    			if (dirty & /*storageProvider*/ 512 && t1_value !== (t1_value = /*storageProvider*/ ctx[9].provider_service_name + "")) set_data_dev(t1, t1_value);
    		},
    		d: function destroy(detaching) {
    			if (detaching) detach_dev(div);
    			mounted = false;
    			dispose();
    		}
    	};

    	dispatch_dev("SvelteRegisterBlock", {
    		block,
    		id: create_if_block_3$4.name,
    		type: "if",
    		source: "(73:4) {#if storageProvider}",
    		ctx
    	});

    	return block;
    }

    // (83:22) 
    function create_if_block_2$6(ctx) {
    	let helpbutton;
    	let current;

    	helpbutton = new HelpButton({
    			props: {
    				key: /*helpKey*/ ctx[6],
    				desc: /*helpDesc*/ ctx[8]
    			},
    			$$inline: true
    		});

    	const block = {
    		c: function create() {
    			create_component(helpbutton.$$.fragment);
    		},
    		m: function mount(target, anchor) {
    			mount_component(helpbutton, target, anchor);
    			current = true;
    		},
    		p: function update(ctx, dirty) {
    			const helpbutton_changes = {};
    			if (dirty & /*helpKey*/ 64) helpbutton_changes.key = /*helpKey*/ ctx[6];
    			if (dirty & /*helpDesc*/ 256) helpbutton_changes.desc = /*helpDesc*/ ctx[8];
    			helpbutton.$set(helpbutton_changes);
    		},
    		i: function intro(local) {
    			if (current) return;
    			transition_in(helpbutton.$$.fragment, local);
    			current = true;
    		},
    		o: function outro(local) {
    			transition_out(helpbutton.$$.fragment, local);
    			current = false;
    		},
    		d: function destroy(detaching) {
    			destroy_component(helpbutton, detaching);
    		}
    	};

    	dispatch_dev("SvelteRegisterBlock", {
    		block,
    		id: create_if_block_2$6.name,
    		type: "if",
    		source: "(83:22) ",
    		ctx
    	});

    	return block;
    }

    // (81:4) {#if helpURL}
    function create_if_block_1$9(ctx) {
    	let helpbutton;
    	let current;

    	helpbutton = new HelpButton({
    			props: {
    				url: /*helpURL*/ ctx[7],
    				desc: /*helpDesc*/ ctx[8]
    			},
    			$$inline: true
    		});

    	const block = {
    		c: function create() {
    			create_component(helpbutton.$$.fragment);
    		},
    		m: function mount(target, anchor) {
    			mount_component(helpbutton, target, anchor);
    			current = true;
    		},
    		p: function update(ctx, dirty) {
    			const helpbutton_changes = {};
    			if (dirty & /*helpURL*/ 128) helpbutton_changes.url = /*helpURL*/ ctx[7];
    			if (dirty & /*helpDesc*/ 256) helpbutton_changes.desc = /*helpDesc*/ ctx[8];
    			helpbutton.$set(helpbutton_changes);
    		},
    		i: function intro(local) {
    			if (current) return;
    			transition_in(helpbutton.$$.fragment, local);
    			current = true;
    		},
    		o: function outro(local) {
    			transition_out(helpbutton.$$.fragment, local);
    			current = false;
    		},
    		d: function destroy(detaching) {
    			destroy_component(helpbutton, detaching);
    		}
    	};

    	dispatch_dev("SvelteRegisterBlock", {
    		block,
    		id: create_if_block_1$9.name,
    		type: "if",
    		source: "(81:4) {#if helpURL}",
    		ctx
    	});

    	return block;
    }

    // (63:3) <PanelRow header>
    function create_default_slot_1$b(ctx) {
    	let current_block_type_index;
    	let if_block0;
    	let t0;
    	let definedinwpconfig;
    	let t1;
    	let t2;
    	let current_block_type_index_1;
    	let if_block2;
    	let if_block2_anchor;
    	let current;
    	const if_block_creators = [create_if_block_4$4, create_else_block$3];
    	const if_blocks = [];

    	function select_block_type_1(ctx, dirty) {
    		if (/*toggleName*/ ctx[5]) return 0;
    		return 1;
    	}

    	current_block_type_index = select_block_type_1(ctx);
    	if_block0 = if_blocks[current_block_type_index] = if_block_creators[current_block_type_index](ctx);

    	definedinwpconfig = new DefinedInWPConfig({
    			props: { defined: /*defined*/ ctx[3] },
    			$$inline: true
    		});

    	let if_block1 = /*storageProvider*/ ctx[9] && create_if_block_3$4(ctx);
    	const if_block_creators_1 = [create_if_block_1$9, create_if_block_2$6];
    	const if_blocks_1 = [];

    	function select_block_type_2(ctx, dirty) {
    		if (/*helpURL*/ ctx[7]) return 0;
    		if (/*helpKey*/ ctx[6]) return 1;
    		return -1;
    	}

    	if (~(current_block_type_index_1 = select_block_type_2(ctx))) {
    		if_block2 = if_blocks_1[current_block_type_index_1] = if_block_creators_1[current_block_type_index_1](ctx);
    	}

    	const block = {
    		c: function create() {
    			if_block0.c();
    			t0 = space();
    			create_component(definedinwpconfig.$$.fragment);
    			t1 = space();
    			if (if_block1) if_block1.c();
    			t2 = space();
    			if (if_block2) if_block2.c();
    			if_block2_anchor = empty();
    		},
    		m: function mount(target, anchor) {
    			if_blocks[current_block_type_index].m(target, anchor);
    			insert_dev(target, t0, anchor);
    			mount_component(definedinwpconfig, target, anchor);
    			insert_dev(target, t1, anchor);
    			if (if_block1) if_block1.m(target, anchor);
    			insert_dev(target, t2, anchor);

    			if (~current_block_type_index_1) {
    				if_blocks_1[current_block_type_index_1].m(target, anchor);
    			}

    			insert_dev(target, if_block2_anchor, anchor);
    			current = true;
    		},
    		p: function update(ctx, dirty) {
    			let previous_block_index = current_block_type_index;
    			current_block_type_index = select_block_type_1(ctx);

    			if (current_block_type_index === previous_block_index) {
    				if_blocks[current_block_type_index].p(ctx, dirty);
    			} else {
    				group_outros();

    				transition_out(if_blocks[previous_block_index], 1, 1, () => {
    					if_blocks[previous_block_index] = null;
    				});

    				check_outros();
    				if_block0 = if_blocks[current_block_type_index];

    				if (!if_block0) {
    					if_block0 = if_blocks[current_block_type_index] = if_block_creators[current_block_type_index](ctx);
    					if_block0.c();
    				} else {
    					if_block0.p(ctx, dirty);
    				}

    				transition_in(if_block0, 1);
    				if_block0.m(t0.parentNode, t0);
    			}

    			const definedinwpconfig_changes = {};
    			if (dirty & /*defined*/ 8) definedinwpconfig_changes.defined = /*defined*/ ctx[3];
    			definedinwpconfig.$set(definedinwpconfig_changes);

    			if (/*storageProvider*/ ctx[9]) {
    				if (if_block1) {
    					if_block1.p(ctx, dirty);
    				} else {
    					if_block1 = create_if_block_3$4(ctx);
    					if_block1.c();
    					if_block1.m(t2.parentNode, t2);
    				}
    			} else if (if_block1) {
    				if_block1.d(1);
    				if_block1 = null;
    			}

    			let previous_block_index_1 = current_block_type_index_1;
    			current_block_type_index_1 = select_block_type_2(ctx);

    			if (current_block_type_index_1 === previous_block_index_1) {
    				if (~current_block_type_index_1) {
    					if_blocks_1[current_block_type_index_1].p(ctx, dirty);
    				}
    			} else {
    				if (if_block2) {
    					group_outros();

    					transition_out(if_blocks_1[previous_block_index_1], 1, 1, () => {
    						if_blocks_1[previous_block_index_1] = null;
    					});

    					check_outros();
    				}

    				if (~current_block_type_index_1) {
    					if_block2 = if_blocks_1[current_block_type_index_1];

    					if (!if_block2) {
    						if_block2 = if_blocks_1[current_block_type_index_1] = if_block_creators_1[current_block_type_index_1](ctx);
    						if_block2.c();
    					} else {
    						if_block2.p(ctx, dirty);
    					}

    					transition_in(if_block2, 1);
    					if_block2.m(if_block2_anchor.parentNode, if_block2_anchor);
    				} else {
    					if_block2 = null;
    				}
    			}
    		},
    		i: function intro(local) {
    			if (current) return;
    			transition_in(if_block0);
    			transition_in(definedinwpconfig.$$.fragment, local);
    			transition_in(if_block2);
    			current = true;
    		},
    		o: function outro(local) {
    			transition_out(if_block0);
    			transition_out(definedinwpconfig.$$.fragment, local);
    			transition_out(if_block2);
    			current = false;
    		},
    		d: function destroy(detaching) {
    			if_blocks[current_block_type_index].d(detaching);
    			if (detaching) detach_dev(t0);
    			destroy_component(definedinwpconfig, detaching);
    			if (detaching) detach_dev(t1);
    			if (if_block1) if_block1.d(detaching);
    			if (detaching) detach_dev(t2);

    			if (~current_block_type_index_1) {
    				if_blocks_1[current_block_type_index_1].d(detaching);
    			}

    			if (detaching) detach_dev(if_block2_anchor);
    		}
    	};

    	dispatch_dev("SvelteRegisterBlock", {
    		block,
    		id: create_default_slot_1$b.name,
    		type: "slot",
    		source: "(63:3) <PanelRow header>",
    		ctx
    	});

    	return block;
    }

    // (61:1) <PanelContainer class={classes}>
    function create_default_slot$n(ctx) {
    	let t;
    	let current;
    	let if_block = /*multi*/ ctx[4] && /*heading*/ ctx[2] && create_if_block$g(ctx);
    	const default_slot_template = /*#slots*/ ctx[17].default;
    	const default_slot = create_slot(default_slot_template, ctx, /*$$scope*/ ctx[19], null);

    	const block = {
    		c: function create() {
    			if (if_block) if_block.c();
    			t = space();
    			if (default_slot) default_slot.c();
    		},
    		m: function mount(target, anchor) {
    			if (if_block) if_block.m(target, anchor);
    			insert_dev(target, t, anchor);

    			if (default_slot) {
    				default_slot.m(target, anchor);
    			}

    			current = true;
    		},
    		p: function update(ctx, dirty) {
    			if (/*multi*/ ctx[4] && /*heading*/ ctx[2]) {
    				if (if_block) {
    					if_block.p(ctx, dirty);

    					if (dirty & /*multi, heading*/ 20) {
    						transition_in(if_block, 1);
    					}
    				} else {
    					if_block = create_if_block$g(ctx);
    					if_block.c();
    					transition_in(if_block, 1);
    					if_block.m(t.parentNode, t);
    				}
    			} else if (if_block) {
    				group_outros();

    				transition_out(if_block, 1, 1, () => {
    					if_block = null;
    				});

    				check_outros();
    			}

    			if (default_slot) {
    				if (default_slot.p && (!current || dirty & /*$$scope*/ 524288)) {
    					update_slot_base(
    						default_slot,
    						default_slot_template,
    						ctx,
    						/*$$scope*/ ctx[19],
    						!current
    						? get_all_dirty_from_scope(/*$$scope*/ ctx[19])
    						: get_slot_changes(default_slot_template, /*$$scope*/ ctx[19], dirty, null),
    						null
    					);
    				}
    			}
    		},
    		i: function intro(local) {
    			if (current) return;
    			transition_in(if_block);
    			transition_in(default_slot, local);
    			current = true;
    		},
    		o: function outro(local) {
    			transition_out(if_block);
    			transition_out(default_slot, local);
    			current = false;
    		},
    		d: function destroy(detaching) {
    			if (if_block) if_block.d(detaching);
    			if (detaching) detach_dev(t);
    			if (default_slot) default_slot.d(detaching);
    		}
    	};

    	dispatch_dev("SvelteRegisterBlock", {
    		block,
    		id: create_default_slot$n.name,
    		type: "slot",
    		source: "(61:1) <PanelContainer class={classes}>",
    		ctx
    	});

    	return block;
    }

    function create_fragment$G(ctx) {
    	let div;
    	let t;
    	let panelcontainer;
    	let div_class_value;
    	let current;
    	let if_block = !/*multi*/ ctx[4] && /*heading*/ ctx[2] && create_if_block_5$3(ctx);

    	panelcontainer = new PanelContainer({
    			props: {
    				class: /*classes*/ ctx[13],
    				$$slots: { default: [create_default_slot$n] },
    				$$scope: { ctx }
    			},
    			$$inline: true
    		});

    	const block = {
    		c: function create() {
    			div = element("div");
    			if (if_block) if_block.c();
    			t = space();
    			create_component(panelcontainer.$$.fragment);
    			attr_dev(div, "class", div_class_value = "panel " + /*name*/ ctx[1] + " svelte-k1tgof");
    			toggle_class(div, "multi", /*multi*/ ctx[4]);
    			toggle_class(div, "locked", /*locked*/ ctx[10]);
    			add_location(div, file$z, 48, 0, 1529);
    		},
    		l: function claim(nodes) {
    			throw new Error("options.hydrate only works if the component was compiled with the `hydratable: true` option");
    		},
    		m: function mount(target, anchor) {
    			insert_dev(target, div, anchor);
    			if (if_block) if_block.m(div, null);
    			append_dev(div, t);
    			mount_component(panelcontainer, div, null);
    			current = true;
    		},
    		p: function update(ctx, [dirty]) {
    			if (!/*multi*/ ctx[4] && /*heading*/ ctx[2]) {
    				if (if_block) {
    					if_block.p(ctx, dirty);

    					if (dirty & /*multi, heading*/ 20) {
    						transition_in(if_block, 1);
    					}
    				} else {
    					if_block = create_if_block_5$3(ctx);
    					if_block.c();
    					transition_in(if_block, 1);
    					if_block.m(div, t);
    				}
    			} else if (if_block) {
    				group_outros();

    				transition_out(if_block, 1, 1, () => {
    					if_block = null;
    				});

    				check_outros();
    			}

    			const panelcontainer_changes = {};

    			if (dirty & /*$$scope, helpURL, helpDesc, helpKey, storageProvider, defined, toggleDisabled, heading, toggleName, toggle, multi*/ 529405) {
    				panelcontainer_changes.$$scope = { dirty, ctx };
    			}

    			panelcontainer.$set(panelcontainer_changes);

    			if (!current || dirty & /*name*/ 2 && div_class_value !== (div_class_value = "panel " + /*name*/ ctx[1] + " svelte-k1tgof")) {
    				attr_dev(div, "class", div_class_value);
    			}

    			if (dirty & /*name, multi*/ 18) {
    				toggle_class(div, "multi", /*multi*/ ctx[4]);
    			}

    			if (dirty & /*name, locked*/ 1026) {
    				toggle_class(div, "locked", /*locked*/ ctx[10]);
    			}
    		},
    		i: function intro(local) {
    			if (current) return;
    			transition_in(if_block);
    			transition_in(panelcontainer.$$.fragment, local);
    			current = true;
    		},
    		o: function outro(local) {
    			transition_out(if_block);
    			transition_out(panelcontainer.$$.fragment, local);
    			current = false;
    		},
    		d: function destroy(detaching) {
    			if (detaching) detach_dev(div);
    			if (if_block) if_block.d();
    			destroy_component(panelcontainer);
    		}
    	};

    	dispatch_dev("SvelteRegisterBlock", {
    		block,
    		id: create_fragment$G.name,
    		type: "component",
    		source: "",
    		ctx
    	});

    	return block;
    }

    function instance$G($$self, $$props, $$invalidate) {
    	let locked;
    	let toggleDisabled;
    	let $defined_settings;

    	let $settingsLocked,
    		$$unsubscribe_settingsLocked = noop,
    		$$subscribe_settingsLocked = () => ($$unsubscribe_settingsLocked(), $$unsubscribe_settingsLocked = subscribe(settingsLocked, $$value => $$invalidate(16, $settingsLocked = $$value)), settingsLocked);

    	let $strings;
    	validate_store(defined_settings, 'defined_settings');
    	component_subscribe($$self, defined_settings, $$value => $$invalidate(15, $defined_settings = $$value));
    	validate_store(strings, 'strings');
    	component_subscribe($$self, strings, $$value => $$invalidate(20, $strings = $$value));
    	$$self.$$.on_destroy.push(() => $$unsubscribe_settingsLocked());
    	let { $$slots: slots = {}, $$scope } = $$props;
    	validate_slots('Panel', slots, ['default']);
    	const classes = $$props.class ? $$props.class : "";
    	let { name = "" } = $$props;
    	let { heading = "" } = $$props;
    	let { defined = false } = $$props;
    	let { multi = false } = $$props;
    	let { toggleName = "" } = $$props;
    	let { toggle = false } = $$props;
    	let { helpKey = "" } = $$props;
    	let { helpURL = "" } = $$props;
    	let { helpDesc = $strings.help_desc } = $$props;
    	let { storageProvider = null } = $$props;

    	// Parent page may want to be locked.
    	let settingsLocked = writable(false);

    	validate_store(settingsLocked, 'settingsLocked');
    	$$subscribe_settingsLocked();

    	if (hasContext("settingsLocked")) {
    		$$subscribe_settingsLocked(settingsLocked = getContext("settingsLocked"));
    	}

    	/**
     * If appropriate, clicking the header toggles to toggle switch.
     */
    	function headingClickHandler() {
    		if (toggleName && !toggleDisabled) {
    			$$invalidate(0, toggle = !toggle);
    		}
    	}

    	function toggleswitch_checked_binding(value) {
    		toggle = value;
    		$$invalidate(0, toggle);
    	}

    	$$self.$$set = $$new_props => {
    		$$invalidate(21, $$props = assign(assign({}, $$props), exclude_internal_props($$new_props)));
    		if ('name' in $$new_props) $$invalidate(1, name = $$new_props.name);
    		if ('heading' in $$new_props) $$invalidate(2, heading = $$new_props.heading);
    		if ('defined' in $$new_props) $$invalidate(3, defined = $$new_props.defined);
    		if ('multi' in $$new_props) $$invalidate(4, multi = $$new_props.multi);
    		if ('toggleName' in $$new_props) $$invalidate(5, toggleName = $$new_props.toggleName);
    		if ('toggle' in $$new_props) $$invalidate(0, toggle = $$new_props.toggle);
    		if ('helpKey' in $$new_props) $$invalidate(6, helpKey = $$new_props.helpKey);
    		if ('helpURL' in $$new_props) $$invalidate(7, helpURL = $$new_props.helpURL);
    		if ('helpDesc' in $$new_props) $$invalidate(8, helpDesc = $$new_props.helpDesc);
    		if ('storageProvider' in $$new_props) $$invalidate(9, storageProvider = $$new_props.storageProvider);
    		if ('$$scope' in $$new_props) $$invalidate(19, $$scope = $$new_props.$$scope);
    	};

    	$$self.$capture_state = () => ({
    		getContext,
    		hasContext,
    		writable,
    		link,
    		defined_settings,
    		strings,
    		PanelContainer,
    		PanelRow,
    		DefinedInWPConfig,
    		ToggleSwitch,
    		HelpButton,
    		classes,
    		name,
    		heading,
    		defined,
    		multi,
    		toggleName,
    		toggle,
    		helpKey,
    		helpURL,
    		helpDesc,
    		storageProvider,
    		settingsLocked,
    		headingClickHandler,
    		toggleDisabled,
    		locked,
    		$defined_settings,
    		$settingsLocked,
    		$strings
    	});

    	$$self.$inject_state = $$new_props => {
    		$$invalidate(21, $$props = assign(assign({}, $$props), $$new_props));
    		if ('name' in $$props) $$invalidate(1, name = $$new_props.name);
    		if ('heading' in $$props) $$invalidate(2, heading = $$new_props.heading);
    		if ('defined' in $$props) $$invalidate(3, defined = $$new_props.defined);
    		if ('multi' in $$props) $$invalidate(4, multi = $$new_props.multi);
    		if ('toggleName' in $$props) $$invalidate(5, toggleName = $$new_props.toggleName);
    		if ('toggle' in $$props) $$invalidate(0, toggle = $$new_props.toggle);
    		if ('helpKey' in $$props) $$invalidate(6, helpKey = $$new_props.helpKey);
    		if ('helpURL' in $$props) $$invalidate(7, helpURL = $$new_props.helpURL);
    		if ('helpDesc' in $$props) $$invalidate(8, helpDesc = $$new_props.helpDesc);
    		if ('storageProvider' in $$props) $$invalidate(9, storageProvider = $$new_props.storageProvider);
    		if ('settingsLocked' in $$props) $$subscribe_settingsLocked($$invalidate(11, settingsLocked = $$new_props.settingsLocked));
    		if ('toggleDisabled' in $$props) $$invalidate(12, toggleDisabled = $$new_props.toggleDisabled);
    		if ('locked' in $$props) $$invalidate(10, locked = $$new_props.locked);
    	};

    	if ($$props && "$$inject" in $$props) {
    		$$self.$inject_state($$props.$$inject);
    	}

    	$$self.$$.update = () => {
    		if ($$self.$$.dirty & /*$settingsLocked*/ 65536) {
    			$$invalidate(10, locked = $settingsLocked);
    		}

    		if ($$self.$$.dirty & /*$defined_settings, toggleName, locked*/ 33824) {
    			$$invalidate(12, toggleDisabled = $defined_settings.includes(toggleName) || locked);
    		}
    	};

    	$$props = exclude_internal_props($$props);

    	return [
    		toggle,
    		name,
    		heading,
    		defined,
    		multi,
    		toggleName,
    		helpKey,
    		helpURL,
    		helpDesc,
    		storageProvider,
    		locked,
    		settingsLocked,
    		toggleDisabled,
    		classes,
    		headingClickHandler,
    		$defined_settings,
    		$settingsLocked,
    		slots,
    		toggleswitch_checked_binding,
    		$$scope
    	];
    }

    class Panel extends SvelteComponentDev {
    	constructor(options) {
    		super(options);

    		init(this, options, instance$G, create_fragment$G, safe_not_equal, {
    			name: 1,
    			heading: 2,
    			defined: 3,
    			multi: 4,
    			toggleName: 5,
    			toggle: 0,
    			helpKey: 6,
    			helpURL: 7,
    			helpDesc: 8,
    			storageProvider: 9
    		});

    		dispatch_dev("SvelteRegisterComponent", {
    			component: this,
    			tagName: "Panel",
    			options,
    			id: create_fragment$G.name
    		});
    	}

    	get name() {
    		throw new Error("<Panel>: Props cannot be read directly from the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
    	}

    	set name(value) {
    		throw new Error("<Panel>: Props cannot be set directly on the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
    	}

    	get heading() {
    		throw new Error("<Panel>: Props cannot be read directly from the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
    	}

    	set heading(value) {
    		throw new Error("<Panel>: Props cannot be set directly on the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
    	}

    	get defined() {
    		throw new Error("<Panel>: Props cannot be read directly from the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
    	}

    	set defined(value) {
    		throw new Error("<Panel>: Props cannot be set directly on the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
    	}

    	get multi() {
    		throw new Error("<Panel>: Props cannot be read directly from the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
    	}

    	set multi(value) {
    		throw new Error("<Panel>: Props cannot be set directly on the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
    	}

    	get toggleName() {
    		throw new Error("<Panel>: Props cannot be read directly from the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
    	}

    	set toggleName(value) {
    		throw new Error("<Panel>: Props cannot be set directly on the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
    	}

    	get toggle() {
    		throw new Error("<Panel>: Props cannot be read directly from the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
    	}

    	set toggle(value) {
    		throw new Error("<Panel>: Props cannot be set directly on the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
    	}

    	get helpKey() {
    		throw new Error("<Panel>: Props cannot be read directly from the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
    	}

    	set helpKey(value) {
    		throw new Error("<Panel>: Props cannot be set directly on the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
    	}

    	get helpURL() {
    		throw new Error("<Panel>: Props cannot be read directly from the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
    	}

    	set helpURL(value) {
    		throw new Error("<Panel>: Props cannot be set directly on the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
    	}

    	get helpDesc() {
    		throw new Error("<Panel>: Props cannot be read directly from the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
    	}

    	set helpDesc(value) {
    		throw new Error("<Panel>: Props cannot be set directly on the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
    	}

    	get storageProvider() {
    		throw new Error("<Panel>: Props cannot be read directly from the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
    	}

    	set storageProvider(value) {
    		throw new Error("<Panel>: Props cannot be set directly on the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
    	}
    }

    /* src/amazon-s3-and-cloudfront/ui/components/Button.svelte generated by Svelte v3.49.0 */

    const file$y = "src/amazon-s3-and-cloudfront/ui/components/Button.svelte";

    function create_fragment$F(ctx) {
    	let button;
    	let current;
    	let mounted;
    	let dispose;
    	const default_slot_template = /*#slots*/ ctx[10].default;
    	const default_slot = create_slot(default_slot_template, ctx, /*$$scope*/ ctx[9], null);

    	const block = {
    		c: function create() {
    			button = element("button");
    			if (default_slot) default_slot.c();
    			attr_dev(button, "class", /*classes*/ ctx[8]);
    			attr_dev(button, "title", /*title*/ ctx[7]);
    			button.disabled = /*disabled*/ ctx[6];
    			toggle_class(button, "btn-xs", /*extraSmall*/ ctx[0]);
    			toggle_class(button, "btn-sm", /*small*/ ctx[1]);
    			toggle_class(button, "btn-md", /*medium*/ ctx[3]);
    			toggle_class(button, "btn-lg", /*large*/ ctx[2]);
    			toggle_class(button, "btn-primary", /*primary*/ ctx[4]);
    			toggle_class(button, "btn-outline", /*outline*/ ctx[5]);
    			toggle_class(button, "btn-disabled", /*disabled*/ ctx[6]);
    			add_location(button, file$y, 20, 0, 538);
    		},
    		l: function claim(nodes) {
    			throw new Error("options.hydrate only works if the component was compiled with the `hydratable: true` option");
    		},
    		m: function mount(target, anchor) {
    			insert_dev(target, button, anchor);

    			if (default_slot) {
    				default_slot.m(button, null);
    			}

    			current = true;

    			if (!mounted) {
    				dispose = listen_dev(button, "click", /*click_handler*/ ctx[11], false, false, false);
    				mounted = true;
    			}
    		},
    		p: function update(ctx, [dirty]) {
    			if (default_slot) {
    				if (default_slot.p && (!current || dirty & /*$$scope*/ 512)) {
    					update_slot_base(
    						default_slot,
    						default_slot_template,
    						ctx,
    						/*$$scope*/ ctx[9],
    						!current
    						? get_all_dirty_from_scope(/*$$scope*/ ctx[9])
    						: get_slot_changes(default_slot_template, /*$$scope*/ ctx[9], dirty, null),
    						null
    					);
    				}
    			}

    			if (!current || dirty & /*title*/ 128) {
    				attr_dev(button, "title", /*title*/ ctx[7]);
    			}

    			if (!current || dirty & /*disabled*/ 64) {
    				prop_dev(button, "disabled", /*disabled*/ ctx[6]);
    			}

    			if (dirty & /*extraSmall*/ 1) {
    				toggle_class(button, "btn-xs", /*extraSmall*/ ctx[0]);
    			}

    			if (dirty & /*small*/ 2) {
    				toggle_class(button, "btn-sm", /*small*/ ctx[1]);
    			}

    			if (dirty & /*medium*/ 8) {
    				toggle_class(button, "btn-md", /*medium*/ ctx[3]);
    			}

    			if (dirty & /*large*/ 4) {
    				toggle_class(button, "btn-lg", /*large*/ ctx[2]);
    			}

    			if (dirty & /*primary*/ 16) {
    				toggle_class(button, "btn-primary", /*primary*/ ctx[4]);
    			}

    			if (dirty & /*outline*/ 32) {
    				toggle_class(button, "btn-outline", /*outline*/ ctx[5]);
    			}

    			if (dirty & /*disabled*/ 64) {
    				toggle_class(button, "btn-disabled", /*disabled*/ ctx[6]);
    			}
    		},
    		i: function intro(local) {
    			if (current) return;
    			transition_in(default_slot, local);
    			current = true;
    		},
    		o: function outro(local) {
    			transition_out(default_slot, local);
    			current = false;
    		},
    		d: function destroy(detaching) {
    			if (detaching) detach_dev(button);
    			if (default_slot) default_slot.d(detaching);
    			mounted = false;
    			dispose();
    		}
    	};

    	dispatch_dev("SvelteRegisterBlock", {
    		block,
    		id: create_fragment$F.name,
    		type: "component",
    		source: "",
    		ctx
    	});

    	return block;
    }

    function instance$F($$self, $$props, $$invalidate) {
    	let { $$slots: slots = {}, $$scope } = $$props;
    	validate_slots('Button', slots, ['default']);
    	const classes = $$props.class ? $$props.class : "";
    	let { extraSmall = false } = $$props;
    	let { small = false } = $$props;
    	let { large = false } = $$props;
    	let { medium = !extraSmall && !small && !large } = $$props;
    	let { primary = false } = $$props;
    	let { outline = !primary } = $$props;
    	let { disabled = false } = $$props;
    	let { title = "" } = $$props;

    	function click_handler(event) {
    		bubble.call(this, $$self, event);
    	}

    	$$self.$$set = $$new_props => {
    		$$invalidate(12, $$props = assign(assign({}, $$props), exclude_internal_props($$new_props)));
    		if ('extraSmall' in $$new_props) $$invalidate(0, extraSmall = $$new_props.extraSmall);
    		if ('small' in $$new_props) $$invalidate(1, small = $$new_props.small);
    		if ('large' in $$new_props) $$invalidate(2, large = $$new_props.large);
    		if ('medium' in $$new_props) $$invalidate(3, medium = $$new_props.medium);
    		if ('primary' in $$new_props) $$invalidate(4, primary = $$new_props.primary);
    		if ('outline' in $$new_props) $$invalidate(5, outline = $$new_props.outline);
    		if ('disabled' in $$new_props) $$invalidate(6, disabled = $$new_props.disabled);
    		if ('title' in $$new_props) $$invalidate(7, title = $$new_props.title);
    		if ('$$scope' in $$new_props) $$invalidate(9, $$scope = $$new_props.$$scope);
    	};

    	$$self.$capture_state = () => ({
    		classes,
    		extraSmall,
    		small,
    		large,
    		medium,
    		primary,
    		outline,
    		disabled,
    		title
    	});

    	$$self.$inject_state = $$new_props => {
    		$$invalidate(12, $$props = assign(assign({}, $$props), $$new_props));
    		if ('extraSmall' in $$props) $$invalidate(0, extraSmall = $$new_props.extraSmall);
    		if ('small' in $$props) $$invalidate(1, small = $$new_props.small);
    		if ('large' in $$props) $$invalidate(2, large = $$new_props.large);
    		if ('medium' in $$props) $$invalidate(3, medium = $$new_props.medium);
    		if ('primary' in $$props) $$invalidate(4, primary = $$new_props.primary);
    		if ('outline' in $$props) $$invalidate(5, outline = $$new_props.outline);
    		if ('disabled' in $$props) $$invalidate(6, disabled = $$new_props.disabled);
    		if ('title' in $$props) $$invalidate(7, title = $$new_props.title);
    	};

    	if ($$props && "$$inject" in $$props) {
    		$$self.$inject_state($$props.$$inject);
    	}

    	$$props = exclude_internal_props($$props);

    	return [
    		extraSmall,
    		small,
    		large,
    		medium,
    		primary,
    		outline,
    		disabled,
    		title,
    		classes,
    		$$scope,
    		slots,
    		click_handler
    	];
    }

    class Button extends SvelteComponentDev {
    	constructor(options) {
    		super(options);

    		init(this, options, instance$F, create_fragment$F, safe_not_equal, {
    			extraSmall: 0,
    			small: 1,
    			large: 2,
    			medium: 3,
    			primary: 4,
    			outline: 5,
    			disabled: 6,
    			title: 7
    		});

    		dispatch_dev("SvelteRegisterComponent", {
    			component: this,
    			tagName: "Button",
    			options,
    			id: create_fragment$F.name
    		});
    	}

    	get extraSmall() {
    		throw new Error("<Button>: Props cannot be read directly from the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
    	}

    	set extraSmall(value) {
    		throw new Error("<Button>: Props cannot be set directly on the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
    	}

    	get small() {
    		throw new Error("<Button>: Props cannot be read directly from the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
    	}

    	set small(value) {
    		throw new Error("<Button>: Props cannot be set directly on the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
    	}

    	get large() {
    		throw new Error("<Button>: Props cannot be read directly from the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
    	}

    	set large(value) {
    		throw new Error("<Button>: Props cannot be set directly on the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
    	}

    	get medium() {
    		throw new Error("<Button>: Props cannot be read directly from the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
    	}

    	set medium(value) {
    		throw new Error("<Button>: Props cannot be set directly on the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
    	}

    	get primary() {
    		throw new Error("<Button>: Props cannot be read directly from the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
    	}

    	set primary(value) {
    		throw new Error("<Button>: Props cannot be set directly on the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
    	}

    	get outline() {
    		throw new Error("<Button>: Props cannot be read directly from the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
    	}

    	set outline(value) {
    		throw new Error("<Button>: Props cannot be set directly on the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
    	}

    	get disabled() {
    		throw new Error("<Button>: Props cannot be read directly from the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
    	}

    	set disabled(value) {
    		throw new Error("<Button>: Props cannot be set directly on the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
    	}

    	get title() {
    		throw new Error("<Button>: Props cannot be read directly from the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
    	}

    	set title(value) {
    		throw new Error("<Button>: Props cannot be set directly on the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
    	}
    }

    /* src/amazon-s3-and-cloudfront/ui/components/StorageSettingsHeadingRow.svelte generated by Svelte v3.49.0 */
    const file$x = "src/amazon-s3-and-cloudfront/ui/components/StorageSettingsHeadingRow.svelte";

    // (32:1) <Button outline on:click={() => push('/storage/provider')} title={$strings.edit_storage_provider} disabled={$settingsLocked}>
    function create_default_slot_1$a(ctx) {
    	let t_value = /*$strings*/ ctx[3].edit + "";
    	let t;

    	const block = {
    		c: function create() {
    			t = text(t_value);
    		},
    		m: function mount(target, anchor) {
    			insert_dev(target, t, anchor);
    		},
    		p: function update(ctx, dirty) {
    			if (dirty & /*$strings*/ 8 && t_value !== (t_value = /*$strings*/ ctx[3].edit + "")) set_data_dev(t, t_value);
    		},
    		d: function destroy(detaching) {
    			if (detaching) detach_dev(t);
    		}
    	};

    	dispatch_dev("SvelteRegisterBlock", {
    		block,
    		id: create_default_slot_1$a.name,
    		type: "slot",
    		source: "(32:1) <Button outline on:click={() => push('/storage/provider')} title={$strings.edit_storage_provider} disabled={$settingsLocked}>",
    		ctx
    	});

    	return block;
    }

    // (23:0) <PanelRow header gradient class="storage {$storage_provider.provider_key_name}">
    function create_default_slot$m(ctx) {
    	let img;
    	let img_src_value;
    	let img_alt_value;
    	let t0;
    	let div;
    	let h4;
    	let t1_value = /*$storage_provider*/ ctx[1].provider_service_name + "";
    	let t1;
    	let t2;
    	let p;
    	let a;
    	let t3_value = /*$settings*/ ctx[4].bucket + "";
    	let t3;
    	let a_href_value;
    	let a_title_value;
    	let t4;
    	let span;
    	let t5;
    	let span_title_value;
    	let t6;
    	let button;
    	let current;

    	button = new Button({
    			props: {
    				outline: true,
    				title: /*$strings*/ ctx[3].edit_storage_provider,
    				disabled: /*$settingsLocked*/ ctx[6],
    				$$slots: { default: [create_default_slot_1$a] },
    				$$scope: { ctx }
    			},
    			$$inline: true
    		});

    	button.$on("click", /*click_handler*/ ctx[7]);

    	const block = {
    		c: function create() {
    			img = element("img");
    			t0 = space();
    			div = element("div");
    			h4 = element("h4");
    			t1 = text(t1_value);
    			t2 = space();
    			p = element("p");
    			a = element("a");
    			t3 = text(t3_value);
    			t4 = space();
    			span = element("span");
    			t5 = text(/*$region_name*/ ctx[5]);
    			t6 = space();
    			create_component(button.$$.fragment);
    			if (!src_url_equal(img.src, img_src_value = /*$storage_provider*/ ctx[1].icon)) attr_dev(img, "src", img_src_value);
    			attr_dev(img, "alt", img_alt_value = /*$storage_provider*/ ctx[1].provider_service_name);
    			attr_dev(img, "class", "svelte-yact8k");
    			add_location(img, file$x, 23, 1, 589);
    			attr_dev(h4, "class", "svelte-yact8k");
    			add_location(h4, file$x, 25, 2, 707);
    			attr_dev(a, "href", a_href_value = /*$urls*/ ctx[2].storage_provider_console_url);
    			attr_dev(a, "class", "console svelte-yact8k");
    			attr_dev(a, "target", "_blank");
    			attr_dev(a, "title", a_title_value = /*$strings*/ ctx[3].view_provider_console);
    			add_location(a, file$x, 27, 3, 791);
    			attr_dev(span, "class", "region svelte-yact8k");
    			attr_dev(span, "title", span_title_value = /*$settings*/ ctx[4].region);
    			add_location(span, file$x, 28, 3, 933);
    			attr_dev(p, "class", "console-details svelte-yact8k");
    			add_location(p, file$x, 26, 2, 760);
    			attr_dev(div, "class", "provider-details svelte-yact8k");
    			add_location(div, file$x, 24, 1, 674);
    		},
    		m: function mount(target, anchor) {
    			insert_dev(target, img, anchor);
    			insert_dev(target, t0, anchor);
    			insert_dev(target, div, anchor);
    			append_dev(div, h4);
    			append_dev(h4, t1);
    			append_dev(div, t2);
    			append_dev(div, p);
    			append_dev(p, a);
    			append_dev(a, t3);
    			append_dev(p, t4);
    			append_dev(p, span);
    			append_dev(span, t5);
    			insert_dev(target, t6, anchor);
    			mount_component(button, target, anchor);
    			current = true;
    		},
    		p: function update(ctx, dirty) {
    			if (!current || dirty & /*$storage_provider*/ 2 && !src_url_equal(img.src, img_src_value = /*$storage_provider*/ ctx[1].icon)) {
    				attr_dev(img, "src", img_src_value);
    			}

    			if (!current || dirty & /*$storage_provider*/ 2 && img_alt_value !== (img_alt_value = /*$storage_provider*/ ctx[1].provider_service_name)) {
    				attr_dev(img, "alt", img_alt_value);
    			}

    			if ((!current || dirty & /*$storage_provider*/ 2) && t1_value !== (t1_value = /*$storage_provider*/ ctx[1].provider_service_name + "")) set_data_dev(t1, t1_value);
    			if ((!current || dirty & /*$settings*/ 16) && t3_value !== (t3_value = /*$settings*/ ctx[4].bucket + "")) set_data_dev(t3, t3_value);

    			if (!current || dirty & /*$urls*/ 4 && a_href_value !== (a_href_value = /*$urls*/ ctx[2].storage_provider_console_url)) {
    				attr_dev(a, "href", a_href_value);
    			}

    			if (!current || dirty & /*$strings*/ 8 && a_title_value !== (a_title_value = /*$strings*/ ctx[3].view_provider_console)) {
    				attr_dev(a, "title", a_title_value);
    			}

    			if (!current || dirty & /*$region_name*/ 32) set_data_dev(t5, /*$region_name*/ ctx[5]);

    			if (!current || dirty & /*$settings*/ 16 && span_title_value !== (span_title_value = /*$settings*/ ctx[4].region)) {
    				attr_dev(span, "title", span_title_value);
    			}

    			const button_changes = {};
    			if (dirty & /*$strings*/ 8) button_changes.title = /*$strings*/ ctx[3].edit_storage_provider;
    			if (dirty & /*$settingsLocked*/ 64) button_changes.disabled = /*$settingsLocked*/ ctx[6];

    			if (dirty & /*$$scope, $strings*/ 264) {
    				button_changes.$$scope = { dirty, ctx };
    			}

    			button.$set(button_changes);
    		},
    		i: function intro(local) {
    			if (current) return;
    			transition_in(button.$$.fragment, local);
    			current = true;
    		},
    		o: function outro(local) {
    			transition_out(button.$$.fragment, local);
    			current = false;
    		},
    		d: function destroy(detaching) {
    			if (detaching) detach_dev(img);
    			if (detaching) detach_dev(t0);
    			if (detaching) detach_dev(div);
    			if (detaching) detach_dev(t6);
    			destroy_component(button, detaching);
    		}
    	};

    	dispatch_dev("SvelteRegisterBlock", {
    		block,
    		id: create_default_slot$m.name,
    		type: "slot",
    		source: "(23:0) <PanelRow header gradient class=\\\"storage {$storage_provider.provider_key_name}\\\">",
    		ctx
    	});

    	return block;
    }

    function create_fragment$E(ctx) {
    	let panelrow;
    	let current;

    	panelrow = new PanelRow({
    			props: {
    				header: true,
    				gradient: true,
    				class: "storage " + /*$storage_provider*/ ctx[1].provider_key_name,
    				$$slots: { default: [create_default_slot$m] },
    				$$scope: { ctx }
    			},
    			$$inline: true
    		});

    	const block = {
    		c: function create() {
    			create_component(panelrow.$$.fragment);
    		},
    		l: function claim(nodes) {
    			throw new Error("options.hydrate only works if the component was compiled with the `hydratable: true` option");
    		},
    		m: function mount(target, anchor) {
    			mount_component(panelrow, target, anchor);
    			current = true;
    		},
    		p: function update(ctx, [dirty]) {
    			const panelrow_changes = {};
    			if (dirty & /*$storage_provider*/ 2) panelrow_changes.class = "storage " + /*$storage_provider*/ ctx[1].provider_key_name;

    			if (dirty & /*$$scope, $strings, $settingsLocked, $settings, $region_name, $urls, $storage_provider*/ 382) {
    				panelrow_changes.$$scope = { dirty, ctx };
    			}

    			panelrow.$set(panelrow_changes);
    		},
    		i: function intro(local) {
    			if (current) return;
    			transition_in(panelrow.$$.fragment, local);
    			current = true;
    		},
    		o: function outro(local) {
    			transition_out(panelrow.$$.fragment, local);
    			current = false;
    		},
    		d: function destroy(detaching) {
    			destroy_component(panelrow, detaching);
    		}
    	};

    	dispatch_dev("SvelteRegisterBlock", {
    		block,
    		id: create_fragment$E.name,
    		type: "component",
    		source: "",
    		ctx
    	});

    	return block;
    }

    function instance$E($$self, $$props, $$invalidate) {
    	let $storage_provider;
    	let $urls;
    	let $strings;
    	let $settings;
    	let $region_name;

    	let $settingsLocked,
    		$$unsubscribe_settingsLocked = noop,
    		$$subscribe_settingsLocked = () => ($$unsubscribe_settingsLocked(), $$unsubscribe_settingsLocked = subscribe(settingsLocked, $$value => $$invalidate(6, $settingsLocked = $$value)), settingsLocked);

    	validate_store(storage_provider, 'storage_provider');
    	component_subscribe($$self, storage_provider, $$value => $$invalidate(1, $storage_provider = $$value));
    	validate_store(urls, 'urls');
    	component_subscribe($$self, urls, $$value => $$invalidate(2, $urls = $$value));
    	validate_store(strings, 'strings');
    	component_subscribe($$self, strings, $$value => $$invalidate(3, $strings = $$value));
    	validate_store(settings, 'settings');
    	component_subscribe($$self, settings, $$value => $$invalidate(4, $settings = $$value));
    	validate_store(region_name, 'region_name');
    	component_subscribe($$self, region_name, $$value => $$invalidate(5, $region_name = $$value));
    	$$self.$$.on_destroy.push(() => $$unsubscribe_settingsLocked());
    	let { $$slots: slots = {}, $$scope } = $$props;
    	validate_slots('StorageSettingsHeadingRow', slots, []);
    	let settingsLocked = writable(false);
    	validate_store(settingsLocked, 'settingsLocked');
    	$$subscribe_settingsLocked();

    	if (hasContext("settingsLocked")) {
    		$$subscribe_settingsLocked(settingsLocked = getContext("settingsLocked"));
    	}

    	const writable_props = [];

    	Object.keys($$props).forEach(key => {
    		if (!~writable_props.indexOf(key) && key.slice(0, 2) !== '$$' && key !== 'slot') console.warn(`<StorageSettingsHeadingRow> was created with unknown prop '${key}'`);
    	});

    	const click_handler = () => push('/storage/provider');

    	$$self.$capture_state = () => ({
    		getContext,
    		hasContext,
    		writable,
    		push,
    		region_name,
    		settings,
    		storage_provider,
    		strings,
    		urls,
    		PanelRow,
    		Button,
    		settingsLocked,
    		$storage_provider,
    		$urls,
    		$strings,
    		$settings,
    		$region_name,
    		$settingsLocked
    	});

    	$$self.$inject_state = $$props => {
    		if ('settingsLocked' in $$props) $$subscribe_settingsLocked($$invalidate(0, settingsLocked = $$props.settingsLocked));
    	};

    	if ($$props && "$$inject" in $$props) {
    		$$self.$inject_state($$props.$$inject);
    	}

    	return [
    		settingsLocked,
    		$storage_provider,
    		$urls,
    		$strings,
    		$settings,
    		$region_name,
    		$settingsLocked,
    		click_handler
    	];
    }

    class StorageSettingsHeadingRow extends SvelteComponentDev {
    	constructor(options) {
    		super(options);
    		init(this, options, instance$E, create_fragment$E, safe_not_equal, {});

    		dispatch_dev("SvelteRegisterComponent", {
    			component: this,
    			tagName: "StorageSettingsHeadingRow",
    			options,
    			id: create_fragment$E.name
    		});
    	}
    }

    /* src/amazon-s3-and-cloudfront/ui/components/SettingNotifications.svelte generated by Svelte v3.49.0 */
    const file$w = "src/amazon-s3-and-cloudfront/ui/components/SettingNotifications.svelte";

    function get_each_context$6(ctx, list, i) {
    	const child_ctx = ctx.slice();
    	child_ctx[2] = list[i];
    	return child_ctx;
    }

    // (48:0) {#if $settings_notifications.has( settingKey )}
    function create_if_block$f(ctx) {
    	let each_blocks = [];
    	let each_1_lookup = new Map();
    	let each_1_anchor;
    	let current;
    	let each_value = [.../*$settings_notifications*/ ctx[1].get(/*settingKey*/ ctx[0]).values()].sort(compareNotificationTypes);
    	validate_each_argument(each_value);
    	const get_key = ctx => /*notification*/ ctx[2];
    	validate_each_keys(ctx, each_value, get_each_context$6, get_key);

    	for (let i = 0; i < each_value.length; i += 1) {
    		let child_ctx = get_each_context$6(ctx, each_value, i);
    		let key = get_key(child_ctx);
    		each_1_lookup.set(key, each_blocks[i] = create_each_block$6(key, child_ctx));
    	}

    	const block = {
    		c: function create() {
    			for (let i = 0; i < each_blocks.length; i += 1) {
    				each_blocks[i].c();
    			}

    			each_1_anchor = empty();
    		},
    		m: function mount(target, anchor) {
    			for (let i = 0; i < each_blocks.length; i += 1) {
    				each_blocks[i].m(target, anchor);
    			}

    			insert_dev(target, each_1_anchor, anchor);
    			current = true;
    		},
    		p: function update(ctx, dirty) {
    			if (dirty & /*$settings_notifications, settingKey, compareNotificationTypes*/ 3) {
    				each_value = [
    					.../*$settings_notifications*/ ctx[1].get(/*settingKey*/ ctx[0]).values()
    				].sort(compareNotificationTypes);

    				validate_each_argument(each_value);
    				group_outros();
    				validate_each_keys(ctx, each_value, get_each_context$6, get_key);
    				each_blocks = update_keyed_each(each_blocks, dirty, get_key, 1, ctx, each_value, each_1_lookup, each_1_anchor.parentNode, outro_and_destroy_block, create_each_block$6, each_1_anchor, get_each_context$6);
    				check_outros();
    			}
    		},
    		i: function intro(local) {
    			if (current) return;

    			for (let i = 0; i < each_value.length; i += 1) {
    				transition_in(each_blocks[i]);
    			}

    			current = true;
    		},
    		o: function outro(local) {
    			for (let i = 0; i < each_blocks.length; i += 1) {
    				transition_out(each_blocks[i]);
    			}

    			current = false;
    		},
    		d: function destroy(detaching) {
    			for (let i = 0; i < each_blocks.length; i += 1) {
    				each_blocks[i].d(detaching);
    			}

    			if (detaching) detach_dev(each_1_anchor);
    		}
    	};

    	dispatch_dev("SvelteRegisterBlock", {
    		block,
    		id: create_if_block$f.name,
    		type: "if",
    		source: "(48:0) {#if $settings_notifications.has( settingKey )}",
    		ctx
    	});

    	return block;
    }

    // (50:2) <Notification {notification}>
    function create_default_slot$l(ctx) {
    	let p;
    	let raw_value = /*notification*/ ctx[2].message + "";
    	let t;

    	const block = {
    		c: function create() {
    			p = element("p");
    			t = space();
    			add_location(p, file$w, 50, 3, 1314);
    		},
    		m: function mount(target, anchor) {
    			insert_dev(target, p, anchor);
    			p.innerHTML = raw_value;
    			insert_dev(target, t, anchor);
    		},
    		p: function update(ctx, dirty) {
    			if (dirty & /*$settings_notifications, settingKey*/ 3 && raw_value !== (raw_value = /*notification*/ ctx[2].message + "")) p.innerHTML = raw_value;		},
    		d: function destroy(detaching) {
    			if (detaching) detach_dev(p);
    			if (detaching) detach_dev(t);
    		}
    	};

    	dispatch_dev("SvelteRegisterBlock", {
    		block,
    		id: create_default_slot$l.name,
    		type: "slot",
    		source: "(50:2) <Notification {notification}>",
    		ctx
    	});

    	return block;
    }

    // (49:1) {#each [...$settings_notifications.get( settingKey ).values()].sort( compareNotificationTypes ) as notification (notification)}
    function create_each_block$6(key_1, ctx) {
    	let first;
    	let notification;
    	let current;

    	notification = new Notification({
    			props: {
    				notification: /*notification*/ ctx[2],
    				$$slots: { default: [create_default_slot$l] },
    				$$scope: { ctx }
    			},
    			$$inline: true
    		});

    	const block = {
    		key: key_1,
    		first: null,
    		c: function create() {
    			first = empty();
    			create_component(notification.$$.fragment);
    			this.first = first;
    		},
    		m: function mount(target, anchor) {
    			insert_dev(target, first, anchor);
    			mount_component(notification, target, anchor);
    			current = true;
    		},
    		p: function update(new_ctx, dirty) {
    			ctx = new_ctx;
    			const notification_changes = {};
    			if (dirty & /*$settings_notifications, settingKey*/ 3) notification_changes.notification = /*notification*/ ctx[2];

    			if (dirty & /*$$scope, $settings_notifications, settingKey*/ 35) {
    				notification_changes.$$scope = { dirty, ctx };
    			}

    			notification.$set(notification_changes);
    		},
    		i: function intro(local) {
    			if (current) return;
    			transition_in(notification.$$.fragment, local);
    			current = true;
    		},
    		o: function outro(local) {
    			transition_out(notification.$$.fragment, local);
    			current = false;
    		},
    		d: function destroy(detaching) {
    			if (detaching) detach_dev(first);
    			destroy_component(notification, detaching);
    		}
    	};

    	dispatch_dev("SvelteRegisterBlock", {
    		block,
    		id: create_each_block$6.name,
    		type: "each",
    		source: "(49:1) {#each [...$settings_notifications.get( settingKey ).values()].sort( compareNotificationTypes ) as notification (notification)}",
    		ctx
    	});

    	return block;
    }

    function create_fragment$D(ctx) {
    	let show_if = /*$settings_notifications*/ ctx[1].has(/*settingKey*/ ctx[0]);
    	let if_block_anchor;
    	let current;
    	let if_block = show_if && create_if_block$f(ctx);

    	const block = {
    		c: function create() {
    			if (if_block) if_block.c();
    			if_block_anchor = empty();
    		},
    		l: function claim(nodes) {
    			throw new Error("options.hydrate only works if the component was compiled with the `hydratable: true` option");
    		},
    		m: function mount(target, anchor) {
    			if (if_block) if_block.m(target, anchor);
    			insert_dev(target, if_block_anchor, anchor);
    			current = true;
    		},
    		p: function update(ctx, [dirty]) {
    			if (dirty & /*$settings_notifications, settingKey*/ 3) show_if = /*$settings_notifications*/ ctx[1].has(/*settingKey*/ ctx[0]);

    			if (show_if) {
    				if (if_block) {
    					if_block.p(ctx, dirty);

    					if (dirty & /*$settings_notifications, settingKey*/ 3) {
    						transition_in(if_block, 1);
    					}
    				} else {
    					if_block = create_if_block$f(ctx);
    					if_block.c();
    					transition_in(if_block, 1);
    					if_block.m(if_block_anchor.parentNode, if_block_anchor);
    				}
    			} else if (if_block) {
    				group_outros();

    				transition_out(if_block, 1, 1, () => {
    					if_block = null;
    				});

    				check_outros();
    			}
    		},
    		i: function intro(local) {
    			if (current) return;
    			transition_in(if_block);
    			current = true;
    		},
    		o: function outro(local) {
    			transition_out(if_block);
    			current = false;
    		},
    		d: function destroy(detaching) {
    			if (if_block) if_block.d(detaching);
    			if (detaching) detach_dev(if_block_anchor);
    		}
    	};

    	dispatch_dev("SvelteRegisterBlock", {
    		block,
    		id: create_fragment$D.name,
    		type: "component",
    		source: "",
    		ctx
    	});

    	return block;
    }

    function compareNotificationTypes(a, b) {
    	// Sort errors to the top.
    	if (a.type === "error" && b.type !== "error") {
    		return -1;
    	}

    	if (b.type === "error" && a.type !== "error") {
    		return 1;
    	}

    	// Next sort warnings.
    	if (a.type === "warning" && b.type !== "warning") {
    		return -1;
    	}

    	if (b.type === "warning" && a.type !== "warning") {
    		return 1;
    	}

    	// Anything else, just sort by type for stability.
    	if (a.type < b.type) {
    		return -1;
    	}

    	if (b.type < a.type) {
    		return 1;
    	}

    	return 0;
    }

    function instance$D($$self, $$props, $$invalidate) {
    	let $settings_notifications;
    	validate_store(settings_notifications, 'settings_notifications');
    	component_subscribe($$self, settings_notifications, $$value => $$invalidate(1, $settings_notifications = $$value));
    	let { $$slots: slots = {}, $$scope } = $$props;
    	validate_slots('SettingNotifications', slots, []);
    	let { settingKey } = $$props;
    	const writable_props = ['settingKey'];

    	Object.keys($$props).forEach(key => {
    		if (!~writable_props.indexOf(key) && key.slice(0, 2) !== '$$' && key !== 'slot') console.warn(`<SettingNotifications> was created with unknown prop '${key}'`);
    	});

    	$$self.$$set = $$props => {
    		if ('settingKey' in $$props) $$invalidate(0, settingKey = $$props.settingKey);
    	};

    	$$self.$capture_state = () => ({
    		settings_notifications,
    		Notification,
    		settingKey,
    		compareNotificationTypes,
    		$settings_notifications
    	});

    	$$self.$inject_state = $$props => {
    		if ('settingKey' in $$props) $$invalidate(0, settingKey = $$props.settingKey);
    	};

    	if ($$props && "$$inject" in $$props) {
    		$$self.$inject_state($$props.$$inject);
    	}

    	return [settingKey, $settings_notifications];
    }

    class SettingNotifications extends SvelteComponentDev {
    	constructor(options) {
    		super(options);
    		init(this, options, instance$D, create_fragment$D, safe_not_equal, { settingKey: 0 });

    		dispatch_dev("SvelteRegisterComponent", {
    			component: this,
    			tagName: "SettingNotifications",
    			options,
    			id: create_fragment$D.name
    		});

    		const { ctx } = this.$$;
    		const props = options.props || {};

    		if (/*settingKey*/ ctx[0] === undefined && !('settingKey' in props)) {
    			console.warn("<SettingNotifications> was created without expected prop 'settingKey'");
    		}
    	}

    	get settingKey() {
    		throw new Error("<SettingNotifications>: Props cannot be read directly from the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
    	}

    	set settingKey(value) {
    		throw new Error("<SettingNotifications>: Props cannot be set directly on the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
    	}
    }

    /* src/amazon-s3-and-cloudfront/ui/components/SettingsPanelOption.svelte generated by Svelte v3.49.0 */
    const file$v = "src/amazon-s3-and-cloudfront/ui/components/SettingsPanelOption.svelte";

    // (95:2) {:else}
    function create_else_block$2(ctx) {
    	let h4;
    	let t;

    	const block = {
    		c: function create() {
    			h4 = element("h4");
    			t = text(/*heading*/ ctx[2]);
    			attr_dev(h4, "id", /*headingName*/ ctx[15]);
    			add_location(h4, file$v, 95, 3, 2604);
    		},
    		m: function mount(target, anchor) {
    			insert_dev(target, h4, anchor);
    			append_dev(h4, t);
    		},
    		p: function update(ctx, dirty) {
    			if (dirty & /*heading*/ 4) set_data_dev(t, /*heading*/ ctx[2]);

    			if (dirty & /*headingName*/ 32768) {
    				attr_dev(h4, "id", /*headingName*/ ctx[15]);
    			}
    		},
    		i: noop,
    		o: noop,
    		d: function destroy(detaching) {
    			if (detaching) detach_dev(h4);
    		}
    	};

    	dispatch_dev("SvelteRegisterBlock", {
    		block,
    		id: create_else_block$2.name,
    		type: "else",
    		source: "(95:2) {:else}",
    		ctx
    	});

    	return block;
    }

    // (90:2) {#if toggleName}
    function create_if_block_4$3(ctx) {
    	let toggleswitch;
    	let updating_checked;
    	let t0;
    	let h4;
    	let t1;
    	let current;
    	let mounted;
    	let dispose;

    	function toggleswitch_checked_binding(value) {
    		/*toggleswitch_checked_binding*/ ctx[23](value);
    	}

    	let toggleswitch_props = {
    		name: /*toggleName*/ ctx[7],
    		disabled: /*toggleDisabled*/ ctx[13],
    		$$slots: { default: [create_default_slot_3$4] },
    		$$scope: { ctx }
    	};

    	if (/*toggle*/ ctx[0] !== void 0) {
    		toggleswitch_props.checked = /*toggle*/ ctx[0];
    	}

    	toggleswitch = new ToggleSwitch({
    			props: toggleswitch_props,
    			$$inline: true
    		});

    	binding_callbacks.push(() => bind(toggleswitch, 'checked', toggleswitch_checked_binding));

    	const block = {
    		c: function create() {
    			create_component(toggleswitch.$$.fragment);
    			t0 = space();
    			h4 = element("h4");
    			t1 = text(/*heading*/ ctx[2]);
    			attr_dev(h4, "id", /*headingName*/ ctx[15]);
    			attr_dev(h4, "class", "toggler svelte-k1tgof");
    			toggle_class(h4, "toggleDisabled", /*toggleDisabled*/ ctx[13]);
    			add_location(h4, file$v, 93, 3, 2487);
    		},
    		m: function mount(target, anchor) {
    			mount_component(toggleswitch, target, anchor);
    			insert_dev(target, t0, anchor);
    			insert_dev(target, h4, anchor);
    			append_dev(h4, t1);
    			current = true;

    			if (!mounted) {
    				dispose = listen_dev(h4, "click", /*headingClickHandler*/ ctx[17], false, false, false);
    				mounted = true;
    			}
    		},
    		p: function update(ctx, dirty) {
    			const toggleswitch_changes = {};
    			if (dirty & /*toggleName*/ 128) toggleswitch_changes.name = /*toggleName*/ ctx[7];
    			if (dirty & /*toggleDisabled*/ 8192) toggleswitch_changes.disabled = /*toggleDisabled*/ ctx[13];

    			if (dirty & /*$$scope, heading*/ 33554436) {
    				toggleswitch_changes.$$scope = { dirty, ctx };
    			}

    			if (!updating_checked && dirty & /*toggle*/ 1) {
    				updating_checked = true;
    				toggleswitch_changes.checked = /*toggle*/ ctx[0];
    				add_flush_callback(() => updating_checked = false);
    			}

    			toggleswitch.$set(toggleswitch_changes);
    			if (!current || dirty & /*heading*/ 4) set_data_dev(t1, /*heading*/ ctx[2]);

    			if (!current || dirty & /*headingName*/ 32768) {
    				attr_dev(h4, "id", /*headingName*/ ctx[15]);
    			}

    			if (dirty & /*toggleDisabled*/ 8192) {
    				toggle_class(h4, "toggleDisabled", /*toggleDisabled*/ ctx[13]);
    			}
    		},
    		i: function intro(local) {
    			if (current) return;
    			transition_in(toggleswitch.$$.fragment, local);
    			current = true;
    		},
    		o: function outro(local) {
    			transition_out(toggleswitch.$$.fragment, local);
    			current = false;
    		},
    		d: function destroy(detaching) {
    			destroy_component(toggleswitch, detaching);
    			if (detaching) detach_dev(t0);
    			if (detaching) detach_dev(h4);
    			mounted = false;
    			dispose();
    		}
    	};

    	dispatch_dev("SvelteRegisterBlock", {
    		block,
    		id: create_if_block_4$3.name,
    		type: "if",
    		source: "(90:2) {#if toggleName}",
    		ctx
    	});

    	return block;
    }

    // (91:3) <ToggleSwitch name={toggleName} bind:checked={toggle} disabled={toggleDisabled}>
    function create_default_slot_3$4(ctx) {
    	let t;

    	const block = {
    		c: function create() {
    			t = text(/*heading*/ ctx[2]);
    		},
    		m: function mount(target, anchor) {
    			insert_dev(target, t, anchor);
    		},
    		p: function update(ctx, dirty) {
    			if (dirty & /*heading*/ 4) set_data_dev(t, /*heading*/ ctx[2]);
    		},
    		d: function destroy(detaching) {
    			if (detaching) detach_dev(t);
    		}
    	};

    	dispatch_dev("SvelteRegisterBlock", {
    		block,
    		id: create_default_slot_3$4.name,
    		type: "slot",
    		source: "(91:3) <ToggleSwitch name={toggleName} bind:checked={toggle} disabled={toggleDisabled}>",
    		ctx
    	});

    	return block;
    }

    // (89:1) <PanelRow class="option">
    function create_default_slot_2$6(ctx) {
    	let current_block_type_index;
    	let if_block;
    	let t;
    	let definedinwpconfig;
    	let current;
    	const if_block_creators = [create_if_block_4$3, create_else_block$2];
    	const if_blocks = [];

    	function select_block_type(ctx, dirty) {
    		if (/*toggleName*/ ctx[7]) return 0;
    		return 1;
    	}

    	current_block_type_index = select_block_type(ctx);
    	if_block = if_blocks[current_block_type_index] = if_block_creators[current_block_type_index](ctx);

    	definedinwpconfig = new DefinedInWPConfig({
    			props: {
    				defined: /*$definedSettings*/ ctx[11].includes(/*toggleName*/ ctx[7]) || /*input*/ ctx[10] && /*$definedSettings*/ ctx[11].includes(/*textName*/ ctx[8])
    			},
    			$$inline: true
    		});

    	const block = {
    		c: function create() {
    			if_block.c();
    			t = space();
    			create_component(definedinwpconfig.$$.fragment);
    		},
    		m: function mount(target, anchor) {
    			if_blocks[current_block_type_index].m(target, anchor);
    			insert_dev(target, t, anchor);
    			mount_component(definedinwpconfig, target, anchor);
    			current = true;
    		},
    		p: function update(ctx, dirty) {
    			let previous_block_index = current_block_type_index;
    			current_block_type_index = select_block_type(ctx);

    			if (current_block_type_index === previous_block_index) {
    				if_blocks[current_block_type_index].p(ctx, dirty);
    			} else {
    				group_outros();

    				transition_out(if_blocks[previous_block_index], 1, 1, () => {
    					if_blocks[previous_block_index] = null;
    				});

    				check_outros();
    				if_block = if_blocks[current_block_type_index];

    				if (!if_block) {
    					if_block = if_blocks[current_block_type_index] = if_block_creators[current_block_type_index](ctx);
    					if_block.c();
    				} else {
    					if_block.p(ctx, dirty);
    				}

    				transition_in(if_block, 1);
    				if_block.m(t.parentNode, t);
    			}

    			const definedinwpconfig_changes = {};
    			if (dirty & /*$definedSettings, toggleName, input, textName*/ 3456) definedinwpconfig_changes.defined = /*$definedSettings*/ ctx[11].includes(/*toggleName*/ ctx[7]) || /*input*/ ctx[10] && /*$definedSettings*/ ctx[11].includes(/*textName*/ ctx[8]);
    			definedinwpconfig.$set(definedinwpconfig_changes);
    		},
    		i: function intro(local) {
    			if (current) return;
    			transition_in(if_block);
    			transition_in(definedinwpconfig.$$.fragment, local);
    			current = true;
    		},
    		o: function outro(local) {
    			transition_out(if_block);
    			transition_out(definedinwpconfig.$$.fragment, local);
    			current = false;
    		},
    		d: function destroy(detaching) {
    			if_blocks[current_block_type_index].d(detaching);
    			if (detaching) detach_dev(t);
    			destroy_component(definedinwpconfig, detaching);
    		}
    	};

    	dispatch_dev("SvelteRegisterBlock", {
    		block,
    		id: create_default_slot_2$6.name,
    		type: "slot",
    		source: "(89:1) <PanelRow class=\\\"option\\\">",
    		ctx
    	});

    	return block;
    }

    // (100:1) <PanelRow class="desc">
    function create_default_slot_1$9(ctx) {
    	let p;

    	const block = {
    		c: function create() {
    			p = element("p");
    			add_location(p, file$v, 100, 2, 2813);
    		},
    		m: function mount(target, anchor) {
    			insert_dev(target, p, anchor);
    			p.innerHTML = /*description*/ ctx[3];
    		},
    		p: function update(ctx, dirty) {
    			if (dirty & /*description*/ 8) p.innerHTML = /*description*/ ctx[3];		},
    		d: function destroy(detaching) {
    			if (detaching) detach_dev(p);
    		}
    	};

    	dispatch_dev("SvelteRegisterBlock", {
    		block,
    		id: create_default_slot_1$9.name,
    		type: "slot",
    		source: "(100:1) <PanelRow class=\\\"desc\\\">",
    		ctx
    	});

    	return block;
    }

    // (103:1) {#if input}
    function create_if_block_2$5(ctx) {
    	let panelrow;
    	let t;
    	let if_block_anchor;
    	let current;

    	panelrow = new PanelRow({
    			props: {
    				class: "input",
    				$$slots: { default: [create_default_slot$k] },
    				$$scope: { ctx }
    			},
    			$$inline: true
    		});

    	let if_block = /*validationError*/ ctx[14] && create_if_block_3$3(ctx);

    	const block = {
    		c: function create() {
    			create_component(panelrow.$$.fragment);
    			t = space();
    			if (if_block) if_block.c();
    			if_block_anchor = empty();
    		},
    		m: function mount(target, anchor) {
    			mount_component(panelrow, target, anchor);
    			insert_dev(target, t, anchor);
    			if (if_block) if_block.m(target, anchor);
    			insert_dev(target, if_block_anchor, anchor);
    			current = true;
    		},
    		p: function update(ctx, dirty) {
    			const panelrow_changes = {};

    			if (dirty & /*$$scope, textName, heading, placeholder, textDisabled, headingName, text*/ 33653014) {
    				panelrow_changes.$$scope = { dirty, ctx };
    			}

    			panelrow.$set(panelrow_changes);

    			if (/*validationError*/ ctx[14]) {
    				if (if_block) {
    					if_block.p(ctx, dirty);

    					if (dirty & /*validationError*/ 16384) {
    						transition_in(if_block, 1);
    					}
    				} else {
    					if_block = create_if_block_3$3(ctx);
    					if_block.c();
    					transition_in(if_block, 1);
    					if_block.m(if_block_anchor.parentNode, if_block_anchor);
    				}
    			} else if (if_block) {
    				group_outros();

    				transition_out(if_block, 1, 1, () => {
    					if_block = null;
    				});

    				check_outros();
    			}
    		},
    		i: function intro(local) {
    			if (current) return;
    			transition_in(panelrow.$$.fragment, local);
    			transition_in(if_block);
    			current = true;
    		},
    		o: function outro(local) {
    			transition_out(panelrow.$$.fragment, local);
    			transition_out(if_block);
    			current = false;
    		},
    		d: function destroy(detaching) {
    			destroy_component(panelrow, detaching);
    			if (detaching) detach_dev(t);
    			if (if_block) if_block.d(detaching);
    			if (detaching) detach_dev(if_block_anchor);
    		}
    	};

    	dispatch_dev("SvelteRegisterBlock", {
    		block,
    		id: create_if_block_2$5.name,
    		type: "if",
    		source: "(103:1) {#if input}",
    		ctx
    	});

    	return block;
    }

    // (104:2) <PanelRow class="input">
    function create_default_slot$k(ctx) {
    	let input_1;
    	let t0;
    	let label;
    	let t1;
    	let mounted;
    	let dispose;

    	const block = {
    		c: function create() {
    			input_1 = element("input");
    			t0 = space();
    			label = element("label");
    			t1 = text(/*heading*/ ctx[2]);
    			attr_dev(input_1, "type", "text");
    			attr_dev(input_1, "id", /*textName*/ ctx[8]);
    			attr_dev(input_1, "name", /*textName*/ ctx[8]);
    			attr_dev(input_1, "minlength", "1");
    			attr_dev(input_1, "size", "10");
    			attr_dev(input_1, "placeholder", /*placeholder*/ ctx[4]);
    			input_1.disabled = /*textDisabled*/ ctx[16];
    			attr_dev(input_1, "aria-labelledby", /*headingName*/ ctx[15]);
    			toggle_class(input_1, "disabled", /*textDisabled*/ ctx[16]);
    			add_location(input_1, file$v, 104, 3, 2896);
    			attr_dev(label, "for", /*textName*/ ctx[8]);
    			add_location(label, file$v, 116, 3, 3133);
    		},
    		m: function mount(target, anchor) {
    			insert_dev(target, input_1, anchor);
    			set_input_value(input_1, /*text*/ ctx[1]);
    			insert_dev(target, t0, anchor);
    			insert_dev(target, label, anchor);
    			append_dev(label, t1);

    			if (!mounted) {
    				dispose = listen_dev(input_1, "input", /*input_1_input_handler*/ ctx[24]);
    				mounted = true;
    			}
    		},
    		p: function update(ctx, dirty) {
    			if (dirty & /*textName*/ 256) {
    				attr_dev(input_1, "id", /*textName*/ ctx[8]);
    			}

    			if (dirty & /*textName*/ 256) {
    				attr_dev(input_1, "name", /*textName*/ ctx[8]);
    			}

    			if (dirty & /*placeholder*/ 16) {
    				attr_dev(input_1, "placeholder", /*placeholder*/ ctx[4]);
    			}

    			if (dirty & /*textDisabled*/ 65536) {
    				prop_dev(input_1, "disabled", /*textDisabled*/ ctx[16]);
    			}

    			if (dirty & /*headingName*/ 32768) {
    				attr_dev(input_1, "aria-labelledby", /*headingName*/ ctx[15]);
    			}

    			if (dirty & /*text*/ 2 && input_1.value !== /*text*/ ctx[1]) {
    				set_input_value(input_1, /*text*/ ctx[1]);
    			}

    			if (dirty & /*textDisabled*/ 65536) {
    				toggle_class(input_1, "disabled", /*textDisabled*/ ctx[16]);
    			}

    			if (dirty & /*heading*/ 4) set_data_dev(t1, /*heading*/ ctx[2]);

    			if (dirty & /*textName*/ 256) {
    				attr_dev(label, "for", /*textName*/ ctx[8]);
    			}
    		},
    		d: function destroy(detaching) {
    			if (detaching) detach_dev(input_1);
    			if (detaching) detach_dev(t0);
    			if (detaching) detach_dev(label);
    			mounted = false;
    			dispose();
    		}
    	};

    	dispatch_dev("SvelteRegisterBlock", {
    		block,
    		id: create_default_slot$k.name,
    		type: "slot",
    		source: "(104:2) <PanelRow class=\\\"input\\\">",
    		ctx
    	});

    	return block;
    }

    // (121:2) {#if validationError}
    function create_if_block_3$3(ctx) {
    	let p;
    	let t;
    	let p_transition;
    	let current;

    	const block = {
    		c: function create() {
    			p = element("p");
    			t = text(/*validationError*/ ctx[14]);
    			attr_dev(p, "class", "input-error");
    			add_location(p, file$v, 121, 3, 3223);
    		},
    		m: function mount(target, anchor) {
    			insert_dev(target, p, anchor);
    			append_dev(p, t);
    			current = true;
    		},
    		p: function update(ctx, dirty) {
    			if (!current || dirty & /*validationError*/ 16384) set_data_dev(t, /*validationError*/ ctx[14]);
    		},
    		i: function intro(local) {
    			if (current) return;

    			if (local) {
    				add_render_callback(() => {
    					if (!p_transition) p_transition = create_bidirectional_transition(p, slide, {}, true);
    					p_transition.run(1);
    				});
    			}

    			current = true;
    		},
    		o: function outro(local) {
    			if (local) {
    				if (!p_transition) p_transition = create_bidirectional_transition(p, slide, {}, false);
    				p_transition.run(0);
    			}

    			current = false;
    		},
    		d: function destroy(detaching) {
    			if (detaching) detach_dev(p);
    			if (detaching && p_transition) p_transition.end();
    		}
    	};

    	dispatch_dev("SvelteRegisterBlock", {
    		block,
    		id: create_if_block_3$3.name,
    		type: "if",
    		source: "(121:2) {#if validationError}",
    		ctx
    	});

    	return block;
    }

    // (126:1) {#if toggleName}
    function create_if_block_1$8(ctx) {
    	let settingnotifications;
    	let current;

    	settingnotifications = new SettingNotifications({
    			props: { settingKey: /*toggleName*/ ctx[7] },
    			$$inline: true
    		});

    	const block = {
    		c: function create() {
    			create_component(settingnotifications.$$.fragment);
    		},
    		m: function mount(target, anchor) {
    			mount_component(settingnotifications, target, anchor);
    			current = true;
    		},
    		p: function update(ctx, dirty) {
    			const settingnotifications_changes = {};
    			if (dirty & /*toggleName*/ 128) settingnotifications_changes.settingKey = /*toggleName*/ ctx[7];
    			settingnotifications.$set(settingnotifications_changes);
    		},
    		i: function intro(local) {
    			if (current) return;
    			transition_in(settingnotifications.$$.fragment, local);
    			current = true;
    		},
    		o: function outro(local) {
    			transition_out(settingnotifications.$$.fragment, local);
    			current = false;
    		},
    		d: function destroy(detaching) {
    			destroy_component(settingnotifications, detaching);
    		}
    	};

    	dispatch_dev("SvelteRegisterBlock", {
    		block,
    		id: create_if_block_1$8.name,
    		type: "if",
    		source: "(126:1) {#if toggleName}",
    		ctx
    	});

    	return block;
    }

    // (130:1) {#if textName}
    function create_if_block$e(ctx) {
    	let settingnotifications;
    	let current;

    	settingnotifications = new SettingNotifications({
    			props: { settingKey: /*textName*/ ctx[8] },
    			$$inline: true
    		});

    	const block = {
    		c: function create() {
    			create_component(settingnotifications.$$.fragment);
    		},
    		m: function mount(target, anchor) {
    			mount_component(settingnotifications, target, anchor);
    			current = true;
    		},
    		p: function update(ctx, dirty) {
    			const settingnotifications_changes = {};
    			if (dirty & /*textName*/ 256) settingnotifications_changes.settingKey = /*textName*/ ctx[8];
    			settingnotifications.$set(settingnotifications_changes);
    		},
    		i: function intro(local) {
    			if (current) return;
    			transition_in(settingnotifications.$$.fragment, local);
    			current = true;
    		},
    		o: function outro(local) {
    			transition_out(settingnotifications.$$.fragment, local);
    			current = false;
    		},
    		d: function destroy(detaching) {
    			destroy_component(settingnotifications, detaching);
    		}
    	};

    	dispatch_dev("SvelteRegisterBlock", {
    		block,
    		id: create_if_block$e.name,
    		type: "if",
    		source: "(130:1) {#if textName}",
    		ctx
    	});

    	return block;
    }

    function create_fragment$C(ctx) {
    	let div;
    	let panelrow0;
    	let t0;
    	let panelrow1;
    	let t1;
    	let t2;
    	let t3;
    	let t4;
    	let current;

    	panelrow0 = new PanelRow({
    			props: {
    				class: "option",
    				$$slots: { default: [create_default_slot_2$6] },
    				$$scope: { ctx }
    			},
    			$$inline: true
    		});

    	panelrow1 = new PanelRow({
    			props: {
    				class: "desc",
    				$$slots: { default: [create_default_slot_1$9] },
    				$$scope: { ctx }
    			},
    			$$inline: true
    		});

    	let if_block0 = /*input*/ ctx[10] && create_if_block_2$5(ctx);
    	let if_block1 = /*toggleName*/ ctx[7] && create_if_block_1$8(ctx);
    	let if_block2 = /*textName*/ ctx[8] && create_if_block$e(ctx);
    	const default_slot_template = /*#slots*/ ctx[22].default;
    	const default_slot = create_slot(default_slot_template, ctx, /*$$scope*/ ctx[25], null);

    	const block = {
    		c: function create() {
    			div = element("div");
    			create_component(panelrow0.$$.fragment);
    			t0 = space();
    			create_component(panelrow1.$$.fragment);
    			t1 = space();
    			if (if_block0) if_block0.c();
    			t2 = space();
    			if (if_block1) if_block1.c();
    			t3 = space();
    			if (if_block2) if_block2.c();
    			t4 = space();
    			if (default_slot) default_slot.c();
    			attr_dev(div, "class", "setting");
    			toggle_class(div, "nested", /*nested*/ ctx[5]);
    			toggle_class(div, "first", /*first*/ ctx[6]);
    			add_location(div, file$v, 87, 0, 2274);
    		},
    		l: function claim(nodes) {
    			throw new Error("options.hydrate only works if the component was compiled with the `hydratable: true` option");
    		},
    		m: function mount(target, anchor) {
    			insert_dev(target, div, anchor);
    			mount_component(panelrow0, div, null);
    			append_dev(div, t0);
    			mount_component(panelrow1, div, null);
    			append_dev(div, t1);
    			if (if_block0) if_block0.m(div, null);
    			append_dev(div, t2);
    			if (if_block1) if_block1.m(div, null);
    			append_dev(div, t3);
    			if (if_block2) if_block2.m(div, null);
    			append_dev(div, t4);

    			if (default_slot) {
    				default_slot.m(div, null);
    			}

    			current = true;
    		},
    		p: function update(ctx, [dirty]) {
    			const panelrow0_changes = {};

    			if (dirty & /*$$scope, $definedSettings, toggleName, input, textName, headingName, toggleDisabled, heading, toggle*/ 33598853) {
    				panelrow0_changes.$$scope = { dirty, ctx };
    			}

    			panelrow0.$set(panelrow0_changes);
    			const panelrow1_changes = {};

    			if (dirty & /*$$scope, description*/ 33554440) {
    				panelrow1_changes.$$scope = { dirty, ctx };
    			}

    			panelrow1.$set(panelrow1_changes);

    			if (/*input*/ ctx[10]) {
    				if (if_block0) {
    					if_block0.p(ctx, dirty);

    					if (dirty & /*input*/ 1024) {
    						transition_in(if_block0, 1);
    					}
    				} else {
    					if_block0 = create_if_block_2$5(ctx);
    					if_block0.c();
    					transition_in(if_block0, 1);
    					if_block0.m(div, t2);
    				}
    			} else if (if_block0) {
    				group_outros();

    				transition_out(if_block0, 1, 1, () => {
    					if_block0 = null;
    				});

    				check_outros();
    			}

    			if (/*toggleName*/ ctx[7]) {
    				if (if_block1) {
    					if_block1.p(ctx, dirty);

    					if (dirty & /*toggleName*/ 128) {
    						transition_in(if_block1, 1);
    					}
    				} else {
    					if_block1 = create_if_block_1$8(ctx);
    					if_block1.c();
    					transition_in(if_block1, 1);
    					if_block1.m(div, t3);
    				}
    			} else if (if_block1) {
    				group_outros();

    				transition_out(if_block1, 1, 1, () => {
    					if_block1 = null;
    				});

    				check_outros();
    			}

    			if (/*textName*/ ctx[8]) {
    				if (if_block2) {
    					if_block2.p(ctx, dirty);

    					if (dirty & /*textName*/ 256) {
    						transition_in(if_block2, 1);
    					}
    				} else {
    					if_block2 = create_if_block$e(ctx);
    					if_block2.c();
    					transition_in(if_block2, 1);
    					if_block2.m(div, t4);
    				}
    			} else if (if_block2) {
    				group_outros();

    				transition_out(if_block2, 1, 1, () => {
    					if_block2 = null;
    				});

    				check_outros();
    			}

    			if (default_slot) {
    				if (default_slot.p && (!current || dirty & /*$$scope*/ 33554432)) {
    					update_slot_base(
    						default_slot,
    						default_slot_template,
    						ctx,
    						/*$$scope*/ ctx[25],
    						!current
    						? get_all_dirty_from_scope(/*$$scope*/ ctx[25])
    						: get_slot_changes(default_slot_template, /*$$scope*/ ctx[25], dirty, null),
    						null
    					);
    				}
    			}

    			if (dirty & /*nested*/ 32) {
    				toggle_class(div, "nested", /*nested*/ ctx[5]);
    			}

    			if (dirty & /*first*/ 64) {
    				toggle_class(div, "first", /*first*/ ctx[6]);
    			}
    		},
    		i: function intro(local) {
    			if (current) return;
    			transition_in(panelrow0.$$.fragment, local);
    			transition_in(panelrow1.$$.fragment, local);
    			transition_in(if_block0);
    			transition_in(if_block1);
    			transition_in(if_block2);
    			transition_in(default_slot, local);
    			current = true;
    		},
    		o: function outro(local) {
    			transition_out(panelrow0.$$.fragment, local);
    			transition_out(panelrow1.$$.fragment, local);
    			transition_out(if_block0);
    			transition_out(if_block1);
    			transition_out(if_block2);
    			transition_out(default_slot, local);
    			current = false;
    		},
    		d: function destroy(detaching) {
    			if (detaching) detach_dev(div);
    			destroy_component(panelrow0);
    			destroy_component(panelrow1);
    			if (if_block0) if_block0.d();
    			if (if_block1) if_block1.d();
    			if (if_block2) if_block2.d();
    			if (default_slot) default_slot.d(detaching);
    		}
    	};

    	dispatch_dev("SvelteRegisterBlock", {
    		block,
    		id: create_fragment$C.name,
    		type: "component",
    		source: "",
    		ctx
    	});

    	return block;
    }

    function instance$C($$self, $$props, $$invalidate) {
    	let locked;
    	let toggleDisabled;
    	let textDisabled;
    	let input;
    	let headingName;
    	let validationError;

    	let $definedSettings,
    		$$unsubscribe_definedSettings = noop,
    		$$subscribe_definedSettings = () => ($$unsubscribe_definedSettings(), $$unsubscribe_definedSettings = subscribe(definedSettings, $$value => $$invalidate(11, $definedSettings = $$value)), definedSettings);

    	let $settingsLocked,
    		$$unsubscribe_settingsLocked = noop,
    		$$subscribe_settingsLocked = () => ($$unsubscribe_settingsLocked(), $$unsubscribe_settingsLocked = subscribe(settingsLocked, $$value => $$invalidate(21, $settingsLocked = $$value)), settingsLocked);

    	$$self.$$.on_destroy.push(() => $$unsubscribe_definedSettings());
    	$$self.$$.on_destroy.push(() => $$unsubscribe_settingsLocked());
    	let { $$slots: slots = {}, $$scope } = $$props;
    	validate_slots('SettingsPanelOption', slots, ['default']);
    	let { heading = "" } = $$props;
    	let { description = "" } = $$props;
    	let { placeholder = "" } = $$props;
    	let { nested = false } = $$props;
    	let { first = false } = $$props;
    	let { toggleName = "" } = $$props;
    	let { toggle = false } = $$props;
    	let { textName = "" } = $$props;
    	let { text = "" } = $$props;
    	let { alwaysShowText = false } = $$props;
    	let { definedSettings = defined_settings } = $$props;
    	validate_store(definedSettings, 'definedSettings');
    	$$subscribe_definedSettings();
    	let { validator = textValue => "" } = $$props;

    	// Parent page may want to be locked.
    	let settingsLocked = writable(false);

    	validate_store(settingsLocked, 'settingsLocked');
    	$$subscribe_settingsLocked();

    	if (hasContext("settingsLocked")) {
    		$$subscribe_settingsLocked(settingsLocked = getContext("settingsLocked"));
    	}

    	/**
     * Validate the text if validator function supplied.
     *
     * @param {string} text
     *
     * @return {string}
     */
    	function validateText(text) {
    		let message = "";

    		if (validator !== undefined) {
    			message = validator(text);
    		}

    		validationErrors.update(_validationErrors => {
    			if (_validationErrors.has(textName) && message === "") {
    				_validationErrors.delete(textName);
    			} else if (message !== "") {
    				_validationErrors.set(textName, message);
    			}

    			return _validationErrors;
    		});

    		return message;
    	}

    	/**
     * If appropriate, clicking the header toggles to toggle switch.
     */
    	function headingClickHandler() {
    		if (toggleName && !toggleDisabled) {
    			$$invalidate(0, toggle = !toggle);
    		}
    	}

    	const writable_props = [
    		'heading',
    		'description',
    		'placeholder',
    		'nested',
    		'first',
    		'toggleName',
    		'toggle',
    		'textName',
    		'text',
    		'alwaysShowText',
    		'definedSettings',
    		'validator'
    	];

    	Object.keys($$props).forEach(key => {
    		if (!~writable_props.indexOf(key) && key.slice(0, 2) !== '$$' && key !== 'slot') console.warn(`<SettingsPanelOption> was created with unknown prop '${key}'`);
    	});

    	function toggleswitch_checked_binding(value) {
    		toggle = value;
    		$$invalidate(0, toggle);
    	}

    	function input_1_input_handler() {
    		text = this.value;
    		$$invalidate(1, text);
    	}

    	$$self.$$set = $$props => {
    		if ('heading' in $$props) $$invalidate(2, heading = $$props.heading);
    		if ('description' in $$props) $$invalidate(3, description = $$props.description);
    		if ('placeholder' in $$props) $$invalidate(4, placeholder = $$props.placeholder);
    		if ('nested' in $$props) $$invalidate(5, nested = $$props.nested);
    		if ('first' in $$props) $$invalidate(6, first = $$props.first);
    		if ('toggleName' in $$props) $$invalidate(7, toggleName = $$props.toggleName);
    		if ('toggle' in $$props) $$invalidate(0, toggle = $$props.toggle);
    		if ('textName' in $$props) $$invalidate(8, textName = $$props.textName);
    		if ('text' in $$props) $$invalidate(1, text = $$props.text);
    		if ('alwaysShowText' in $$props) $$invalidate(18, alwaysShowText = $$props.alwaysShowText);
    		if ('definedSettings' in $$props) $$subscribe_definedSettings($$invalidate(9, definedSettings = $$props.definedSettings));
    		if ('validator' in $$props) $$invalidate(19, validator = $$props.validator);
    		if ('$$scope' in $$props) $$invalidate(25, $$scope = $$props.$$scope);
    	};

    	$$self.$capture_state = () => ({
    		getContext,
    		hasContext,
    		writable,
    		slide,
    		defined_settings,
    		validationErrors,
    		PanelRow,
    		ToggleSwitch,
    		DefinedInWPConfig,
    		SettingNotifications,
    		heading,
    		description,
    		placeholder,
    		nested,
    		first,
    		toggleName,
    		toggle,
    		textName,
    		text,
    		alwaysShowText,
    		definedSettings,
    		validator,
    		settingsLocked,
    		validateText,
    		headingClickHandler,
    		toggleDisabled,
    		validationError,
    		input,
    		headingName,
    		locked,
    		textDisabled,
    		$definedSettings,
    		$settingsLocked
    	});

    	$$self.$inject_state = $$props => {
    		if ('heading' in $$props) $$invalidate(2, heading = $$props.heading);
    		if ('description' in $$props) $$invalidate(3, description = $$props.description);
    		if ('placeholder' in $$props) $$invalidate(4, placeholder = $$props.placeholder);
    		if ('nested' in $$props) $$invalidate(5, nested = $$props.nested);
    		if ('first' in $$props) $$invalidate(6, first = $$props.first);
    		if ('toggleName' in $$props) $$invalidate(7, toggleName = $$props.toggleName);
    		if ('toggle' in $$props) $$invalidate(0, toggle = $$props.toggle);
    		if ('textName' in $$props) $$invalidate(8, textName = $$props.textName);
    		if ('text' in $$props) $$invalidate(1, text = $$props.text);
    		if ('alwaysShowText' in $$props) $$invalidate(18, alwaysShowText = $$props.alwaysShowText);
    		if ('definedSettings' in $$props) $$subscribe_definedSettings($$invalidate(9, definedSettings = $$props.definedSettings));
    		if ('validator' in $$props) $$invalidate(19, validator = $$props.validator);
    		if ('settingsLocked' in $$props) $$subscribe_settingsLocked($$invalidate(12, settingsLocked = $$props.settingsLocked));
    		if ('toggleDisabled' in $$props) $$invalidate(13, toggleDisabled = $$props.toggleDisabled);
    		if ('validationError' in $$props) $$invalidate(14, validationError = $$props.validationError);
    		if ('input' in $$props) $$invalidate(10, input = $$props.input);
    		if ('headingName' in $$props) $$invalidate(15, headingName = $$props.headingName);
    		if ('locked' in $$props) $$invalidate(20, locked = $$props.locked);
    		if ('textDisabled' in $$props) $$invalidate(16, textDisabled = $$props.textDisabled);
    	};

    	if ($$props && "$$inject" in $$props) {
    		$$self.$inject_state($$props.$$inject);
    	}

    	$$self.$$.update = () => {
    		if ($$self.$$.dirty & /*$settingsLocked*/ 2097152) {
    			$$invalidate(20, locked = $settingsLocked);
    		}

    		if ($$self.$$.dirty & /*$definedSettings, toggleName, locked*/ 1050752) {
    			$$invalidate(13, toggleDisabled = $definedSettings.includes(toggleName) || locked);
    		}

    		if ($$self.$$.dirty & /*$definedSettings, textName, locked*/ 1050880) {
    			$$invalidate(16, textDisabled = $definedSettings.includes(textName) || locked);
    		}

    		if ($$self.$$.dirty & /*toggleName, toggle, alwaysShowText, textName*/ 262529) {
    			$$invalidate(10, input = (toggleName && toggle || !toggleName || alwaysShowText) && textName);
    		}

    		if ($$self.$$.dirty & /*input, textName, toggleName*/ 1408) {
    			$$invalidate(15, headingName = input ? textName + "-heading" : toggleName);
    		}

    		if ($$self.$$.dirty & /*text*/ 2) {
    			$$invalidate(14, validationError = validateText(text));
    		}
    	};

    	return [
    		toggle,
    		text,
    		heading,
    		description,
    		placeholder,
    		nested,
    		first,
    		toggleName,
    		textName,
    		definedSettings,
    		input,
    		$definedSettings,
    		settingsLocked,
    		toggleDisabled,
    		validationError,
    		headingName,
    		textDisabled,
    		headingClickHandler,
    		alwaysShowText,
    		validator,
    		locked,
    		$settingsLocked,
    		slots,
    		toggleswitch_checked_binding,
    		input_1_input_handler,
    		$$scope
    	];
    }

    class SettingsPanelOption extends SvelteComponentDev {
    	constructor(options) {
    		super(options);

    		init(this, options, instance$C, create_fragment$C, safe_not_equal, {
    			heading: 2,
    			description: 3,
    			placeholder: 4,
    			nested: 5,
    			first: 6,
    			toggleName: 7,
    			toggle: 0,
    			textName: 8,
    			text: 1,
    			alwaysShowText: 18,
    			definedSettings: 9,
    			validator: 19
    		});

    		dispatch_dev("SvelteRegisterComponent", {
    			component: this,
    			tagName: "SettingsPanelOption",
    			options,
    			id: create_fragment$C.name
    		});
    	}

    	get heading() {
    		throw new Error("<SettingsPanelOption>: Props cannot be read directly from the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
    	}

    	set heading(value) {
    		throw new Error("<SettingsPanelOption>: Props cannot be set directly on the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
    	}

    	get description() {
    		throw new Error("<SettingsPanelOption>: Props cannot be read directly from the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
    	}

    	set description(value) {
    		throw new Error("<SettingsPanelOption>: Props cannot be set directly on the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
    	}

    	get placeholder() {
    		throw new Error("<SettingsPanelOption>: Props cannot be read directly from the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
    	}

    	set placeholder(value) {
    		throw new Error("<SettingsPanelOption>: Props cannot be set directly on the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
    	}

    	get nested() {
    		throw new Error("<SettingsPanelOption>: Props cannot be read directly from the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
    	}

    	set nested(value) {
    		throw new Error("<SettingsPanelOption>: Props cannot be set directly on the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
    	}

    	get first() {
    		throw new Error("<SettingsPanelOption>: Props cannot be read directly from the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
    	}

    	set first(value) {
    		throw new Error("<SettingsPanelOption>: Props cannot be set directly on the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
    	}

    	get toggleName() {
    		throw new Error("<SettingsPanelOption>: Props cannot be read directly from the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
    	}

    	set toggleName(value) {
    		throw new Error("<SettingsPanelOption>: Props cannot be set directly on the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
    	}

    	get toggle() {
    		throw new Error("<SettingsPanelOption>: Props cannot be read directly from the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
    	}

    	set toggle(value) {
    		throw new Error("<SettingsPanelOption>: Props cannot be set directly on the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
    	}

    	get textName() {
    		throw new Error("<SettingsPanelOption>: Props cannot be read directly from the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
    	}

    	set textName(value) {
    		throw new Error("<SettingsPanelOption>: Props cannot be set directly on the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
    	}

    	get text() {
    		throw new Error("<SettingsPanelOption>: Props cannot be read directly from the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
    	}

    	set text(value) {
    		throw new Error("<SettingsPanelOption>: Props cannot be set directly on the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
    	}

    	get alwaysShowText() {
    		throw new Error("<SettingsPanelOption>: Props cannot be read directly from the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
    	}

    	set alwaysShowText(value) {
    		throw new Error("<SettingsPanelOption>: Props cannot be set directly on the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
    	}

    	get definedSettings() {
    		throw new Error("<SettingsPanelOption>: Props cannot be read directly from the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
    	}

    	set definedSettings(value) {
    		throw new Error("<SettingsPanelOption>: Props cannot be set directly on the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
    	}

    	get validator() {
    		throw new Error("<SettingsPanelOption>: Props cannot be read directly from the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
    	}

    	set validator(value) {
    		throw new Error("<SettingsPanelOption>: Props cannot be set directly on the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
    	}
    }

    /* src/amazon-s3-and-cloudfront/ui/components/StorageSettingsPanel.svelte generated by Svelte v3.49.0 */

    // (8:0) <Panel name="settings" heading={$strings.storage_settings_title} helpKey="storage-provider">
    function create_default_slot$j(ctx) {
    	let storagesettingsheadingrow;
    	let t0;
    	let settingspaneloption0;
    	let updating_toggle;
    	let t1;
    	let settingspaneloption1;
    	let updating_toggle_1;
    	let t2;
    	let settingspaneloption2;
    	let updating_toggle_2;
    	let updating_text;
    	let t3;
    	let settingspaneloption3;
    	let updating_toggle_3;
    	let t4;
    	let settingspaneloption4;
    	let updating_toggle_4;
    	let current;
    	storagesettingsheadingrow = new StorageSettingsHeadingRow({ $$inline: true });

    	function settingspaneloption0_toggle_binding(value) {
    		/*settingspaneloption0_toggle_binding*/ ctx[2](value);
    	}

    	let settingspaneloption0_props = {
    		heading: /*$strings*/ ctx[0].copy_files_to_bucket,
    		description: /*$strings*/ ctx[0].copy_files_to_bucket_desc,
    		toggleName: "copy-to-s3"
    	};

    	if (/*$settings*/ ctx[1]["copy-to-s3"] !== void 0) {
    		settingspaneloption0_props.toggle = /*$settings*/ ctx[1]["copy-to-s3"];
    	}

    	settingspaneloption0 = new SettingsPanelOption({
    			props: settingspaneloption0_props,
    			$$inline: true
    		});

    	binding_callbacks.push(() => bind(settingspaneloption0, 'toggle', settingspaneloption0_toggle_binding));

    	function settingspaneloption1_toggle_binding(value) {
    		/*settingspaneloption1_toggle_binding*/ ctx[3](value);
    	}

    	let settingspaneloption1_props = {
    		heading: /*$strings*/ ctx[0].remove_local_file,
    		description: /*$strings*/ ctx[0].remove_local_file_desc,
    		toggleName: "remove-local-file"
    	};

    	if (/*$settings*/ ctx[1]["remove-local-file"] !== void 0) {
    		settingspaneloption1_props.toggle = /*$settings*/ ctx[1]["remove-local-file"];
    	}

    	settingspaneloption1 = new SettingsPanelOption({
    			props: settingspaneloption1_props,
    			$$inline: true
    		});

    	binding_callbacks.push(() => bind(settingspaneloption1, 'toggle', settingspaneloption1_toggle_binding));

    	function settingspaneloption2_toggle_binding(value) {
    		/*settingspaneloption2_toggle_binding*/ ctx[4](value);
    	}

    	function settingspaneloption2_text_binding(value) {
    		/*settingspaneloption2_text_binding*/ ctx[5](value);
    	}

    	let settingspaneloption2_props = {
    		heading: /*$strings*/ ctx[0].path,
    		description: /*$strings*/ ctx[0].path_desc,
    		toggleName: "enable-object-prefix",
    		textName: "object-prefix"
    	};

    	if (/*$settings*/ ctx[1]["enable-object-prefix"] !== void 0) {
    		settingspaneloption2_props.toggle = /*$settings*/ ctx[1]["enable-object-prefix"];
    	}

    	if (/*$settings*/ ctx[1]["object-prefix"] !== void 0) {
    		settingspaneloption2_props.text = /*$settings*/ ctx[1]["object-prefix"];
    	}

    	settingspaneloption2 = new SettingsPanelOption({
    			props: settingspaneloption2_props,
    			$$inline: true
    		});

    	binding_callbacks.push(() => bind(settingspaneloption2, 'toggle', settingspaneloption2_toggle_binding));
    	binding_callbacks.push(() => bind(settingspaneloption2, 'text', settingspaneloption2_text_binding));

    	function settingspaneloption3_toggle_binding(value) {
    		/*settingspaneloption3_toggle_binding*/ ctx[6](value);
    	}

    	let settingspaneloption3_props = {
    		heading: /*$strings*/ ctx[0].year_month,
    		description: /*$strings*/ ctx[0].year_month_desc,
    		toggleName: "use-yearmonth-folders"
    	};

    	if (/*$settings*/ ctx[1]["use-yearmonth-folders"] !== void 0) {
    		settingspaneloption3_props.toggle = /*$settings*/ ctx[1]["use-yearmonth-folders"];
    	}

    	settingspaneloption3 = new SettingsPanelOption({
    			props: settingspaneloption3_props,
    			$$inline: true
    		});

    	binding_callbacks.push(() => bind(settingspaneloption3, 'toggle', settingspaneloption3_toggle_binding));

    	function settingspaneloption4_toggle_binding(value) {
    		/*settingspaneloption4_toggle_binding*/ ctx[7](value);
    	}

    	let settingspaneloption4_props = {
    		heading: /*$strings*/ ctx[0].object_versioning,
    		description: /*$strings*/ ctx[0].object_versioning_desc,
    		toggleName: "object-versioning"
    	};

    	if (/*$settings*/ ctx[1]["object-versioning"] !== void 0) {
    		settingspaneloption4_props.toggle = /*$settings*/ ctx[1]["object-versioning"];
    	}

    	settingspaneloption4 = new SettingsPanelOption({
    			props: settingspaneloption4_props,
    			$$inline: true
    		});

    	binding_callbacks.push(() => bind(settingspaneloption4, 'toggle', settingspaneloption4_toggle_binding));

    	const block = {
    		c: function create() {
    			create_component(storagesettingsheadingrow.$$.fragment);
    			t0 = space();
    			create_component(settingspaneloption0.$$.fragment);
    			t1 = space();
    			create_component(settingspaneloption1.$$.fragment);
    			t2 = space();
    			create_component(settingspaneloption2.$$.fragment);
    			t3 = space();
    			create_component(settingspaneloption3.$$.fragment);
    			t4 = space();
    			create_component(settingspaneloption4.$$.fragment);
    		},
    		m: function mount(target, anchor) {
    			mount_component(storagesettingsheadingrow, target, anchor);
    			insert_dev(target, t0, anchor);
    			mount_component(settingspaneloption0, target, anchor);
    			insert_dev(target, t1, anchor);
    			mount_component(settingspaneloption1, target, anchor);
    			insert_dev(target, t2, anchor);
    			mount_component(settingspaneloption2, target, anchor);
    			insert_dev(target, t3, anchor);
    			mount_component(settingspaneloption3, target, anchor);
    			insert_dev(target, t4, anchor);
    			mount_component(settingspaneloption4, target, anchor);
    			current = true;
    		},
    		p: function update(ctx, dirty) {
    			const settingspaneloption0_changes = {};
    			if (dirty & /*$strings*/ 1) settingspaneloption0_changes.heading = /*$strings*/ ctx[0].copy_files_to_bucket;
    			if (dirty & /*$strings*/ 1) settingspaneloption0_changes.description = /*$strings*/ ctx[0].copy_files_to_bucket_desc;

    			if (!updating_toggle && dirty & /*$settings*/ 2) {
    				updating_toggle = true;
    				settingspaneloption0_changes.toggle = /*$settings*/ ctx[1]["copy-to-s3"];
    				add_flush_callback(() => updating_toggle = false);
    			}

    			settingspaneloption0.$set(settingspaneloption0_changes);
    			const settingspaneloption1_changes = {};
    			if (dirty & /*$strings*/ 1) settingspaneloption1_changes.heading = /*$strings*/ ctx[0].remove_local_file;
    			if (dirty & /*$strings*/ 1) settingspaneloption1_changes.description = /*$strings*/ ctx[0].remove_local_file_desc;

    			if (!updating_toggle_1 && dirty & /*$settings*/ 2) {
    				updating_toggle_1 = true;
    				settingspaneloption1_changes.toggle = /*$settings*/ ctx[1]["remove-local-file"];
    				add_flush_callback(() => updating_toggle_1 = false);
    			}

    			settingspaneloption1.$set(settingspaneloption1_changes);
    			const settingspaneloption2_changes = {};
    			if (dirty & /*$strings*/ 1) settingspaneloption2_changes.heading = /*$strings*/ ctx[0].path;
    			if (dirty & /*$strings*/ 1) settingspaneloption2_changes.description = /*$strings*/ ctx[0].path_desc;

    			if (!updating_toggle_2 && dirty & /*$settings*/ 2) {
    				updating_toggle_2 = true;
    				settingspaneloption2_changes.toggle = /*$settings*/ ctx[1]["enable-object-prefix"];
    				add_flush_callback(() => updating_toggle_2 = false);
    			}

    			if (!updating_text && dirty & /*$settings*/ 2) {
    				updating_text = true;
    				settingspaneloption2_changes.text = /*$settings*/ ctx[1]["object-prefix"];
    				add_flush_callback(() => updating_text = false);
    			}

    			settingspaneloption2.$set(settingspaneloption2_changes);
    			const settingspaneloption3_changes = {};
    			if (dirty & /*$strings*/ 1) settingspaneloption3_changes.heading = /*$strings*/ ctx[0].year_month;
    			if (dirty & /*$strings*/ 1) settingspaneloption3_changes.description = /*$strings*/ ctx[0].year_month_desc;

    			if (!updating_toggle_3 && dirty & /*$settings*/ 2) {
    				updating_toggle_3 = true;
    				settingspaneloption3_changes.toggle = /*$settings*/ ctx[1]["use-yearmonth-folders"];
    				add_flush_callback(() => updating_toggle_3 = false);
    			}

    			settingspaneloption3.$set(settingspaneloption3_changes);
    			const settingspaneloption4_changes = {};
    			if (dirty & /*$strings*/ 1) settingspaneloption4_changes.heading = /*$strings*/ ctx[0].object_versioning;
    			if (dirty & /*$strings*/ 1) settingspaneloption4_changes.description = /*$strings*/ ctx[0].object_versioning_desc;

    			if (!updating_toggle_4 && dirty & /*$settings*/ 2) {
    				updating_toggle_4 = true;
    				settingspaneloption4_changes.toggle = /*$settings*/ ctx[1]["object-versioning"];
    				add_flush_callback(() => updating_toggle_4 = false);
    			}

    			settingspaneloption4.$set(settingspaneloption4_changes);
    		},
    		i: function intro(local) {
    			if (current) return;
    			transition_in(storagesettingsheadingrow.$$.fragment, local);
    			transition_in(settingspaneloption0.$$.fragment, local);
    			transition_in(settingspaneloption1.$$.fragment, local);
    			transition_in(settingspaneloption2.$$.fragment, local);
    			transition_in(settingspaneloption3.$$.fragment, local);
    			transition_in(settingspaneloption4.$$.fragment, local);
    			current = true;
    		},
    		o: function outro(local) {
    			transition_out(storagesettingsheadingrow.$$.fragment, local);
    			transition_out(settingspaneloption0.$$.fragment, local);
    			transition_out(settingspaneloption1.$$.fragment, local);
    			transition_out(settingspaneloption2.$$.fragment, local);
    			transition_out(settingspaneloption3.$$.fragment, local);
    			transition_out(settingspaneloption4.$$.fragment, local);
    			current = false;
    		},
    		d: function destroy(detaching) {
    			destroy_component(storagesettingsheadingrow, detaching);
    			if (detaching) detach_dev(t0);
    			destroy_component(settingspaneloption0, detaching);
    			if (detaching) detach_dev(t1);
    			destroy_component(settingspaneloption1, detaching);
    			if (detaching) detach_dev(t2);
    			destroy_component(settingspaneloption2, detaching);
    			if (detaching) detach_dev(t3);
    			destroy_component(settingspaneloption3, detaching);
    			if (detaching) detach_dev(t4);
    			destroy_component(settingspaneloption4, detaching);
    		}
    	};

    	dispatch_dev("SvelteRegisterBlock", {
    		block,
    		id: create_default_slot$j.name,
    		type: "slot",
    		source: "(8:0) <Panel name=\\\"settings\\\" heading={$strings.storage_settings_title} helpKey=\\\"storage-provider\\\">",
    		ctx
    	});

    	return block;
    }

    function create_fragment$B(ctx) {
    	let panel;
    	let current;

    	panel = new Panel({
    			props: {
    				name: "settings",
    				heading: /*$strings*/ ctx[0].storage_settings_title,
    				helpKey: "storage-provider",
    				$$slots: { default: [create_default_slot$j] },
    				$$scope: { ctx }
    			},
    			$$inline: true
    		});

    	const block = {
    		c: function create() {
    			create_component(panel.$$.fragment);
    		},
    		l: function claim(nodes) {
    			throw new Error("options.hydrate only works if the component was compiled with the `hydratable: true` option");
    		},
    		m: function mount(target, anchor) {
    			mount_component(panel, target, anchor);
    			current = true;
    		},
    		p: function update(ctx, [dirty]) {
    			const panel_changes = {};
    			if (dirty & /*$strings*/ 1) panel_changes.heading = /*$strings*/ ctx[0].storage_settings_title;

    			if (dirty & /*$$scope, $strings, $settings*/ 259) {
    				panel_changes.$$scope = { dirty, ctx };
    			}

    			panel.$set(panel_changes);
    		},
    		i: function intro(local) {
    			if (current) return;
    			transition_in(panel.$$.fragment, local);
    			current = true;
    		},
    		o: function outro(local) {
    			transition_out(panel.$$.fragment, local);
    			current = false;
    		},
    		d: function destroy(detaching) {
    			destroy_component(panel, detaching);
    		}
    	};

    	dispatch_dev("SvelteRegisterBlock", {
    		block,
    		id: create_fragment$B.name,
    		type: "component",
    		source: "",
    		ctx
    	});

    	return block;
    }

    function instance$B($$self, $$props, $$invalidate) {
    	let $strings;
    	let $settings;
    	validate_store(strings, 'strings');
    	component_subscribe($$self, strings, $$value => $$invalidate(0, $strings = $$value));
    	validate_store(settings, 'settings');
    	component_subscribe($$self, settings, $$value => $$invalidate(1, $settings = $$value));
    	let { $$slots: slots = {}, $$scope } = $$props;
    	validate_slots('StorageSettingsPanel', slots, []);
    	const writable_props = [];

    	Object.keys($$props).forEach(key => {
    		if (!~writable_props.indexOf(key) && key.slice(0, 2) !== '$$' && key !== 'slot') console.warn(`<StorageSettingsPanel> was created with unknown prop '${key}'`);
    	});

    	function settingspaneloption0_toggle_binding(value) {
    		if ($$self.$$.not_equal($settings["copy-to-s3"], value)) {
    			$settings["copy-to-s3"] = value;
    			settings.set($settings);
    		}
    	}

    	function settingspaneloption1_toggle_binding(value) {
    		if ($$self.$$.not_equal($settings["remove-local-file"], value)) {
    			$settings["remove-local-file"] = value;
    			settings.set($settings);
    		}
    	}

    	function settingspaneloption2_toggle_binding(value) {
    		if ($$self.$$.not_equal($settings["enable-object-prefix"], value)) {
    			$settings["enable-object-prefix"] = value;
    			settings.set($settings);
    		}
    	}

    	function settingspaneloption2_text_binding(value) {
    		if ($$self.$$.not_equal($settings["object-prefix"], value)) {
    			$settings["object-prefix"] = value;
    			settings.set($settings);
    		}
    	}

    	function settingspaneloption3_toggle_binding(value) {
    		if ($$self.$$.not_equal($settings["use-yearmonth-folders"], value)) {
    			$settings["use-yearmonth-folders"] = value;
    			settings.set($settings);
    		}
    	}

    	function settingspaneloption4_toggle_binding(value) {
    		if ($$self.$$.not_equal($settings["object-versioning"], value)) {
    			$settings["object-versioning"] = value;
    			settings.set($settings);
    		}
    	}

    	$$self.$capture_state = () => ({
    		settings,
    		strings,
    		Panel,
    		StorageSettingsHeadingRow,
    		SettingsPanelOption,
    		$strings,
    		$settings
    	});

    	return [
    		$strings,
    		$settings,
    		settingspaneloption0_toggle_binding,
    		settingspaneloption1_toggle_binding,
    		settingspaneloption2_toggle_binding,
    		settingspaneloption2_text_binding,
    		settingspaneloption3_toggle_binding,
    		settingspaneloption4_toggle_binding
    	];
    }

    class StorageSettingsPanel extends SvelteComponentDev {
    	constructor(options) {
    		super(options);
    		init(this, options, instance$B, create_fragment$B, safe_not_equal, {});

    		dispatch_dev("SvelteRegisterComponent", {
    			component: this,
    			tagName: "StorageSettingsPanel",
    			options,
    			id: create_fragment$B.name
    		});
    	}
    }

    /* src/amazon-s3-and-cloudfront/ui/components/StorageSettingsSubPage.svelte generated by Svelte v3.49.0 */

    // (6:0) <SubPage name="storage-settings">
    function create_default_slot$i(ctx) {
    	let storagesettingspanel;
    	let current;
    	storagesettingspanel = new StorageSettingsPanel({ $$inline: true });

    	const block = {
    		c: function create() {
    			create_component(storagesettingspanel.$$.fragment);
    		},
    		m: function mount(target, anchor) {
    			mount_component(storagesettingspanel, target, anchor);
    			current = true;
    		},
    		i: function intro(local) {
    			if (current) return;
    			transition_in(storagesettingspanel.$$.fragment, local);
    			current = true;
    		},
    		o: function outro(local) {
    			transition_out(storagesettingspanel.$$.fragment, local);
    			current = false;
    		},
    		d: function destroy(detaching) {
    			destroy_component(storagesettingspanel, detaching);
    		}
    	};

    	dispatch_dev("SvelteRegisterBlock", {
    		block,
    		id: create_default_slot$i.name,
    		type: "slot",
    		source: "(6:0) <SubPage name=\\\"storage-settings\\\">",
    		ctx
    	});

    	return block;
    }

    function create_fragment$A(ctx) {
    	let subpage;
    	let current;

    	subpage = new SubPage({
    			props: {
    				name: "storage-settings",
    				$$slots: { default: [create_default_slot$i] },
    				$$scope: { ctx }
    			},
    			$$inline: true
    		});

    	const block = {
    		c: function create() {
    			create_component(subpage.$$.fragment);
    		},
    		l: function claim(nodes) {
    			throw new Error("options.hydrate only works if the component was compiled with the `hydratable: true` option");
    		},
    		m: function mount(target, anchor) {
    			mount_component(subpage, target, anchor);
    			current = true;
    		},
    		p: function update(ctx, [dirty]) {
    			const subpage_changes = {};

    			if (dirty & /*$$scope*/ 1) {
    				subpage_changes.$$scope = { dirty, ctx };
    			}

    			subpage.$set(subpage_changes);
    		},
    		i: function intro(local) {
    			if (current) return;
    			transition_in(subpage.$$.fragment, local);
    			current = true;
    		},
    		o: function outro(local) {
    			transition_out(subpage.$$.fragment, local);
    			current = false;
    		},
    		d: function destroy(detaching) {
    			destroy_component(subpage, detaching);
    		}
    	};

    	dispatch_dev("SvelteRegisterBlock", {
    		block,
    		id: create_fragment$A.name,
    		type: "component",
    		source: "",
    		ctx
    	});

    	return block;
    }

    function instance$A($$self, $$props, $$invalidate) {
    	let { $$slots: slots = {}, $$scope } = $$props;
    	validate_slots('StorageSettingsSubPage', slots, []);
    	const writable_props = [];

    	Object.keys($$props).forEach(key => {
    		if (!~writable_props.indexOf(key) && key.slice(0, 2) !== '$$' && key !== 'slot') console.warn(`<StorageSettingsSubPage> was created with unknown prop '${key}'`);
    	});

    	$$self.$capture_state = () => ({ SubPage, StorageSettingsPanel });
    	return [];
    }

    class StorageSettingsSubPage extends SvelteComponentDev {
    	constructor(options) {
    		super(options);
    		init(this, options, instance$A, create_fragment$A, safe_not_equal, {});

    		dispatch_dev("SvelteRegisterComponent", {
    			component: this,
    			tagName: "StorageSettingsSubPage",
    			options,
    			id: create_fragment$A.name
    		});
    	}
    }

    /* src/amazon-s3-and-cloudfront/ui/components/DeliverySettingsHeadingRow.svelte generated by Svelte v3.49.0 */
    const file$u = "src/amazon-s3-and-cloudfront/ui/components/DeliverySettingsHeadingRow.svelte";

    // (34:1) <Button outline on:click={() => push('/delivery/provider')} title={$strings.edit_delivery_provider} disabled={$settingsLocked}>
    function create_default_slot_1$8(ctx) {
    	let t_value = /*$strings*/ ctx[5].edit + "";
    	let t;

    	const block = {
    		c: function create() {
    			t = text(t_value);
    		},
    		m: function mount(target, anchor) {
    			insert_dev(target, t, anchor);
    		},
    		p: function update(ctx, dirty) {
    			if (dirty & /*$strings*/ 32 && t_value !== (t_value = /*$strings*/ ctx[5].edit + "")) set_data_dev(t, t_value);
    		},
    		d: function destroy(detaching) {
    			if (detaching) detach_dev(t);
    		}
    	};

    	dispatch_dev("SvelteRegisterBlock", {
    		block,
    		id: create_default_slot_1$8.name,
    		type: "slot",
    		source: "(34:1) <Button outline on:click={() => push('/delivery/provider')} title={$strings.edit_delivery_provider} disabled={$settingsLocked}>",
    		ctx
    	});

    	return block;
    }

    // (26:0) <PanelRow header gradient class="delivery {providerType} {providerKey}">
    function create_default_slot$h(ctx) {
    	let img;
    	let img_src_value;
    	let img_alt_value;
    	let t0;
    	let div;
    	let h4;
    	let t1_value = /*$delivery_provider*/ ctx[1].provider_service_name + "";
    	let t1;
    	let t2;
    	let p;
    	let a;
    	let t3_value = /*$delivery_provider*/ ctx[1].console_title + "";
    	let t3;
    	let a_href_value;
    	let a_title_value;
    	let t4;
    	let button;
    	let current;

    	button = new Button({
    			props: {
    				outline: true,
    				title: /*$strings*/ ctx[5].edit_delivery_provider,
    				disabled: /*$settingsLocked*/ ctx[6],
    				$$slots: { default: [create_default_slot_1$8] },
    				$$scope: { ctx }
    			},
    			$$inline: true
    		});

    	button.$on("click", /*click_handler*/ ctx[9]);

    	const block = {
    		c: function create() {
    			img = element("img");
    			t0 = space();
    			div = element("div");
    			h4 = element("h4");
    			t1 = text(t1_value);
    			t2 = space();
    			p = element("p");
    			a = element("a");
    			t3 = text(t3_value);
    			t4 = space();
    			create_component(button.$$.fragment);
    			if (!src_url_equal(img.src, img_src_value = /*$delivery_provider*/ ctx[1].icon)) attr_dev(img, "src", img_src_value);
    			attr_dev(img, "alt", img_alt_value = /*$delivery_provider*/ ctx[1].provider_service_name);
    			attr_dev(img, "class", "svelte-54jmjg");
    			add_location(img, file$u, 26, 1, 803);
    			attr_dev(h4, "class", "svelte-54jmjg");
    			add_location(h4, file$u, 28, 2, 923);
    			attr_dev(a, "href", a_href_value = /*$urls*/ ctx[4].delivery_provider_console_url);
    			attr_dev(a, "class", "console svelte-54jmjg");
    			attr_dev(a, "target", "_blank");
    			attr_dev(a, "title", a_title_value = /*$strings*/ ctx[5].view_provider_console);
    			add_location(a, file$u, 30, 3, 1008);
    			attr_dev(p, "class", "console-details svelte-54jmjg");
    			add_location(p, file$u, 29, 2, 977);
    			attr_dev(div, "class", "provider-details svelte-54jmjg");
    			add_location(div, file$u, 27, 1, 890);
    		},
    		m: function mount(target, anchor) {
    			insert_dev(target, img, anchor);
    			insert_dev(target, t0, anchor);
    			insert_dev(target, div, anchor);
    			append_dev(div, h4);
    			append_dev(h4, t1);
    			append_dev(div, t2);
    			append_dev(div, p);
    			append_dev(p, a);
    			append_dev(a, t3);
    			insert_dev(target, t4, anchor);
    			mount_component(button, target, anchor);
    			current = true;
    		},
    		p: function update(ctx, dirty) {
    			if (!current || dirty & /*$delivery_provider*/ 2 && !src_url_equal(img.src, img_src_value = /*$delivery_provider*/ ctx[1].icon)) {
    				attr_dev(img, "src", img_src_value);
    			}

    			if (!current || dirty & /*$delivery_provider*/ 2 && img_alt_value !== (img_alt_value = /*$delivery_provider*/ ctx[1].provider_service_name)) {
    				attr_dev(img, "alt", img_alt_value);
    			}

    			if ((!current || dirty & /*$delivery_provider*/ 2) && t1_value !== (t1_value = /*$delivery_provider*/ ctx[1].provider_service_name + "")) set_data_dev(t1, t1_value);
    			if ((!current || dirty & /*$delivery_provider*/ 2) && t3_value !== (t3_value = /*$delivery_provider*/ ctx[1].console_title + "")) set_data_dev(t3, t3_value);

    			if (!current || dirty & /*$urls*/ 16 && a_href_value !== (a_href_value = /*$urls*/ ctx[4].delivery_provider_console_url)) {
    				attr_dev(a, "href", a_href_value);
    			}

    			if (!current || dirty & /*$strings*/ 32 && a_title_value !== (a_title_value = /*$strings*/ ctx[5].view_provider_console)) {
    				attr_dev(a, "title", a_title_value);
    			}

    			const button_changes = {};
    			if (dirty & /*$strings*/ 32) button_changes.title = /*$strings*/ ctx[5].edit_delivery_provider;
    			if (dirty & /*$settingsLocked*/ 64) button_changes.disabled = /*$settingsLocked*/ ctx[6];

    			if (dirty & /*$$scope, $strings*/ 1056) {
    				button_changes.$$scope = { dirty, ctx };
    			}

    			button.$set(button_changes);
    		},
    		i: function intro(local) {
    			if (current) return;
    			transition_in(button.$$.fragment, local);
    			current = true;
    		},
    		o: function outro(local) {
    			transition_out(button.$$.fragment, local);
    			current = false;
    		},
    		d: function destroy(detaching) {
    			if (detaching) detach_dev(img);
    			if (detaching) detach_dev(t0);
    			if (detaching) detach_dev(div);
    			if (detaching) detach_dev(t4);
    			destroy_component(button, detaching);
    		}
    	};

    	dispatch_dev("SvelteRegisterBlock", {
    		block,
    		id: create_default_slot$h.name,
    		type: "slot",
    		source: "(26:0) <PanelRow header gradient class=\\\"delivery {providerType} {providerKey}\\\">",
    		ctx
    	});

    	return block;
    }

    function create_fragment$z(ctx) {
    	let panelrow;
    	let current;

    	panelrow = new PanelRow({
    			props: {
    				header: true,
    				gradient: true,
    				class: "delivery " + /*providerType*/ ctx[0] + " " + /*providerKey*/ ctx[3],
    				$$slots: { default: [create_default_slot$h] },
    				$$scope: { ctx }
    			},
    			$$inline: true
    		});

    	const block = {
    		c: function create() {
    			create_component(panelrow.$$.fragment);
    		},
    		l: function claim(nodes) {
    			throw new Error("options.hydrate only works if the component was compiled with the `hydratable: true` option");
    		},
    		m: function mount(target, anchor) {
    			mount_component(panelrow, target, anchor);
    			current = true;
    		},
    		p: function update(ctx, [dirty]) {
    			const panelrow_changes = {};
    			if (dirty & /*providerType, providerKey*/ 9) panelrow_changes.class = "delivery " + /*providerType*/ ctx[0] + " " + /*providerKey*/ ctx[3];

    			if (dirty & /*$$scope, $strings, $settingsLocked, $urls, $delivery_provider*/ 1138) {
    				panelrow_changes.$$scope = { dirty, ctx };
    			}

    			panelrow.$set(panelrow_changes);
    		},
    		i: function intro(local) {
    			if (current) return;
    			transition_in(panelrow.$$.fragment, local);
    			current = true;
    		},
    		o: function outro(local) {
    			transition_out(panelrow.$$.fragment, local);
    			current = false;
    		},
    		d: function destroy(detaching) {
    			destroy_component(panelrow, detaching);
    		}
    	};

    	dispatch_dev("SvelteRegisterBlock", {
    		block,
    		id: create_fragment$z.name,
    		type: "component",
    		source: "",
    		ctx
    	});

    	return block;
    }

    function instance$z($$self, $$props, $$invalidate) {
    	let providerType;
    	let providerKey;
    	let $delivery_provider;
    	let $storage_provider;
    	let $settings;
    	let $urls;
    	let $strings;

    	let $settingsLocked,
    		$$unsubscribe_settingsLocked = noop,
    		$$subscribe_settingsLocked = () => ($$unsubscribe_settingsLocked(), $$unsubscribe_settingsLocked = subscribe(settingsLocked, $$value => $$invalidate(6, $settingsLocked = $$value)), settingsLocked);

    	validate_store(delivery_provider, 'delivery_provider');
    	component_subscribe($$self, delivery_provider, $$value => $$invalidate(1, $delivery_provider = $$value));
    	validate_store(storage_provider, 'storage_provider');
    	component_subscribe($$self, storage_provider, $$value => $$invalidate(7, $storage_provider = $$value));
    	validate_store(settings, 'settings');
    	component_subscribe($$self, settings, $$value => $$invalidate(8, $settings = $$value));
    	validate_store(urls, 'urls');
    	component_subscribe($$self, urls, $$value => $$invalidate(4, $urls = $$value));
    	validate_store(strings, 'strings');
    	component_subscribe($$self, strings, $$value => $$invalidate(5, $strings = $$value));
    	$$self.$$.on_destroy.push(() => $$unsubscribe_settingsLocked());
    	let { $$slots: slots = {}, $$scope } = $$props;
    	validate_slots('DeliverySettingsHeadingRow', slots, []);
    	let settingsLocked = writable(false);
    	validate_store(settingsLocked, 'settingsLocked');
    	$$subscribe_settingsLocked();

    	if (hasContext("settingsLocked")) {
    		$$subscribe_settingsLocked(settingsLocked = getContext("settingsLocked"));
    	}

    	const writable_props = [];

    	Object.keys($$props).forEach(key => {
    		if (!~writable_props.indexOf(key) && key.slice(0, 2) !== '$$' && key !== 'slot') console.warn(`<DeliverySettingsHeadingRow> was created with unknown prop '${key}'`);
    	});

    	const click_handler = () => push('/delivery/provider');

    	$$self.$capture_state = () => ({
    		hasContext,
    		getContext,
    		writable,
    		push,
    		delivery_provider,
    		settings,
    		storage_provider,
    		strings,
    		urls,
    		PanelRow,
    		Button,
    		settingsLocked,
    		providerType,
    		providerKey,
    		$delivery_provider,
    		$storage_provider,
    		$settings,
    		$urls,
    		$strings,
    		$settingsLocked
    	});

    	$$self.$inject_state = $$props => {
    		if ('settingsLocked' in $$props) $$subscribe_settingsLocked($$invalidate(2, settingsLocked = $$props.settingsLocked));
    		if ('providerType' in $$props) $$invalidate(0, providerType = $$props.providerType);
    		if ('providerKey' in $$props) $$invalidate(3, providerKey = $$props.providerKey);
    	};

    	if ($$props && "$$inject" in $$props) {
    		$$self.$inject_state($$props.$$inject);
    	}

    	$$self.$$.update = () => {
    		if ($$self.$$.dirty & /*$settings*/ 256) {
    			$$invalidate(0, providerType = $settings['delivery-provider'] === 'storage'
    			? 'storage'
    			: 'delivery');
    		}

    		if ($$self.$$.dirty & /*providerType, $storage_provider, $delivery_provider*/ 131) {
    			$$invalidate(3, providerKey = providerType === 'storage'
    			? $storage_provider.provider_key_name
    			: $delivery_provider.provider_key_name);
    		}
    	};

    	return [
    		providerType,
    		$delivery_provider,
    		settingsLocked,
    		providerKey,
    		$urls,
    		$strings,
    		$settingsLocked,
    		$storage_provider,
    		$settings,
    		click_handler
    	];
    }

    class DeliverySettingsHeadingRow extends SvelteComponentDev {
    	constructor(options) {
    		super(options);
    		init(this, options, instance$z, create_fragment$z, safe_not_equal, {});

    		dispatch_dev("SvelteRegisterComponent", {
    			component: this,
    			tagName: "DeliverySettingsHeadingRow",
    			options,
    			id: create_fragment$z.name
    		});
    	}
    }

    /* src/amazon-s3-and-cloudfront/ui/components/DeliverySettingsPanel.svelte generated by Svelte v3.49.0 */

    // (41:1) {#if $delivery_provider.delivery_domain_allowed}
    function create_if_block$d(ctx) {
    	let settingspaneloption;
    	let updating_toggle;
    	let updating_text;
    	let t;
    	let if_block_anchor;
    	let current;

    	function settingspaneloption_toggle_binding(value) {
    		/*settingspaneloption_toggle_binding*/ ctx[5](value);
    	}

    	function settingspaneloption_text_binding(value) {
    		/*settingspaneloption_text_binding*/ ctx[6](value);
    	}

    	let settingspaneloption_props = {
    		heading: /*$strings*/ ctx[0].delivery_domain,
    		description: /*$delivery_provider*/ ctx[1].delivery_domain_desc,
    		toggleName: "enable-delivery-domain",
    		textName: "delivery-domain",
    		validator: /*domainValidator*/ ctx[3]
    	};

    	if (/*$settings*/ ctx[2]["enable-delivery-domain"] !== void 0) {
    		settingspaneloption_props.toggle = /*$settings*/ ctx[2]["enable-delivery-domain"];
    	}

    	if (/*$settings*/ ctx[2]["delivery-domain"] !== void 0) {
    		settingspaneloption_props.text = /*$settings*/ ctx[2]["delivery-domain"];
    	}

    	settingspaneloption = new SettingsPanelOption({
    			props: settingspaneloption_props,
    			$$inline: true
    		});

    	binding_callbacks.push(() => bind(settingspaneloption, 'toggle', settingspaneloption_toggle_binding));
    	binding_callbacks.push(() => bind(settingspaneloption, 'text', settingspaneloption_text_binding));
    	let if_block = /*$delivery_provider*/ ctx[1].use_signed_urls_key_file_allowed && /*$settings*/ ctx[2]["enable-delivery-domain"] && create_if_block_1$7(ctx);

    	const block = {
    		c: function create() {
    			create_component(settingspaneloption.$$.fragment);
    			t = space();
    			if (if_block) if_block.c();
    			if_block_anchor = empty();
    		},
    		m: function mount(target, anchor) {
    			mount_component(settingspaneloption, target, anchor);
    			insert_dev(target, t, anchor);
    			if (if_block) if_block.m(target, anchor);
    			insert_dev(target, if_block_anchor, anchor);
    			current = true;
    		},
    		p: function update(ctx, dirty) {
    			const settingspaneloption_changes = {};
    			if (dirty & /*$strings*/ 1) settingspaneloption_changes.heading = /*$strings*/ ctx[0].delivery_domain;
    			if (dirty & /*$delivery_provider*/ 2) settingspaneloption_changes.description = /*$delivery_provider*/ ctx[1].delivery_domain_desc;

    			if (!updating_toggle && dirty & /*$settings*/ 4) {
    				updating_toggle = true;
    				settingspaneloption_changes.toggle = /*$settings*/ ctx[2]["enable-delivery-domain"];
    				add_flush_callback(() => updating_toggle = false);
    			}

    			if (!updating_text && dirty & /*$settings*/ 4) {
    				updating_text = true;
    				settingspaneloption_changes.text = /*$settings*/ ctx[2]["delivery-domain"];
    				add_flush_callback(() => updating_text = false);
    			}

    			settingspaneloption.$set(settingspaneloption_changes);

    			if (/*$delivery_provider*/ ctx[1].use_signed_urls_key_file_allowed && /*$settings*/ ctx[2]["enable-delivery-domain"]) {
    				if (if_block) {
    					if_block.p(ctx, dirty);

    					if (dirty & /*$delivery_provider, $settings*/ 6) {
    						transition_in(if_block, 1);
    					}
    				} else {
    					if_block = create_if_block_1$7(ctx);
    					if_block.c();
    					transition_in(if_block, 1);
    					if_block.m(if_block_anchor.parentNode, if_block_anchor);
    				}
    			} else if (if_block) {
    				group_outros();

    				transition_out(if_block, 1, 1, () => {
    					if_block = null;
    				});

    				check_outros();
    			}
    		},
    		i: function intro(local) {
    			if (current) return;
    			transition_in(settingspaneloption.$$.fragment, local);
    			transition_in(if_block);
    			current = true;
    		},
    		o: function outro(local) {
    			transition_out(settingspaneloption.$$.fragment, local);
    			transition_out(if_block);
    			current = false;
    		},
    		d: function destroy(detaching) {
    			destroy_component(settingspaneloption, detaching);
    			if (detaching) detach_dev(t);
    			if (if_block) if_block.d(detaching);
    			if (detaching) detach_dev(if_block_anchor);
    		}
    	};

    	dispatch_dev("SvelteRegisterBlock", {
    		block,
    		id: create_if_block$d.name,
    		type: "if",
    		source: "(41:1) {#if $delivery_provider.delivery_domain_allowed}",
    		ctx
    	});

    	return block;
    }

    // (51:2) {#if $delivery_provider.use_signed_urls_key_file_allowed && $settings[ "enable-delivery-domain" ]}
    function create_if_block_1$7(ctx) {
    	let settingspaneloption;
    	let updating_toggle;
    	let current;

    	function settingspaneloption_toggle_binding_1(value) {
    		/*settingspaneloption_toggle_binding_1*/ ctx[10](value);
    	}

    	let settingspaneloption_props = {
    		heading: /*$delivery_provider*/ ctx[1].signed_urls_option_name,
    		description: /*$delivery_provider*/ ctx[1].signed_urls_option_description,
    		toggleName: "enable-signed-urls",
    		$$slots: { default: [create_default_slot_1$7] },
    		$$scope: { ctx }
    	};

    	if (/*$settings*/ ctx[2]["enable-signed-urls"] !== void 0) {
    		settingspaneloption_props.toggle = /*$settings*/ ctx[2]["enable-signed-urls"];
    	}

    	settingspaneloption = new SettingsPanelOption({
    			props: settingspaneloption_props,
    			$$inline: true
    		});

    	binding_callbacks.push(() => bind(settingspaneloption, 'toggle', settingspaneloption_toggle_binding_1));

    	const block = {
    		c: function create() {
    			create_component(settingspaneloption.$$.fragment);
    		},
    		m: function mount(target, anchor) {
    			mount_component(settingspaneloption, target, anchor);
    			current = true;
    		},
    		p: function update(ctx, dirty) {
    			const settingspaneloption_changes = {};
    			if (dirty & /*$delivery_provider*/ 2) settingspaneloption_changes.heading = /*$delivery_provider*/ ctx[1].signed_urls_option_name;
    			if (dirty & /*$delivery_provider*/ 2) settingspaneloption_changes.description = /*$delivery_provider*/ ctx[1].signed_urls_option_description;

    			if (dirty & /*$$scope, $delivery_provider, $settings*/ 4102) {
    				settingspaneloption_changes.$$scope = { dirty, ctx };
    			}

    			if (!updating_toggle && dirty & /*$settings*/ 4) {
    				updating_toggle = true;
    				settingspaneloption_changes.toggle = /*$settings*/ ctx[2]["enable-signed-urls"];
    				add_flush_callback(() => updating_toggle = false);
    			}

    			settingspaneloption.$set(settingspaneloption_changes);
    		},
    		i: function intro(local) {
    			if (current) return;
    			transition_in(settingspaneloption.$$.fragment, local);
    			current = true;
    		},
    		o: function outro(local) {
    			transition_out(settingspaneloption.$$.fragment, local);
    			current = false;
    		},
    		d: function destroy(detaching) {
    			destroy_component(settingspaneloption, detaching);
    		}
    	};

    	dispatch_dev("SvelteRegisterBlock", {
    		block,
    		id: create_if_block_1$7.name,
    		type: "if",
    		source: "(51:2) {#if $delivery_provider.use_signed_urls_key_file_allowed && $settings[ \\\"enable-delivery-domain\\\" ]}",
    		ctx
    	});

    	return block;
    }

    // (59:4) {#if $settings[ "enable-signed-urls" ]}
    function create_if_block_2$4(ctx) {
    	let settingspaneloption0;
    	let updating_text;
    	let t0;
    	let settingspaneloption1;
    	let updating_text_1;
    	let t1;
    	let settingspaneloption2;
    	let updating_text_2;
    	let current;

    	function settingspaneloption0_text_binding(value) {
    		/*settingspaneloption0_text_binding*/ ctx[7](value);
    	}

    	let settingspaneloption0_props = {
    		heading: /*$delivery_provider*/ ctx[1].signed_urls_key_id_name,
    		description: /*$delivery_provider*/ ctx[1].signed_urls_key_id_description,
    		textName: "signed-urls-key-id",
    		nested: true,
    		first: true
    	};

    	if (/*$settings*/ ctx[2]["signed-urls-key-id"] !== void 0) {
    		settingspaneloption0_props.text = /*$settings*/ ctx[2]["signed-urls-key-id"];
    	}

    	settingspaneloption0 = new SettingsPanelOption({
    			props: settingspaneloption0_props,
    			$$inline: true
    		});

    	binding_callbacks.push(() => bind(settingspaneloption0, 'text', settingspaneloption0_text_binding));

    	function settingspaneloption1_text_binding(value) {
    		/*settingspaneloption1_text_binding*/ ctx[8](value);
    	}

    	let settingspaneloption1_props = {
    		heading: /*$delivery_provider*/ ctx[1].signed_urls_key_file_path_name,
    		description: /*$delivery_provider*/ ctx[1].signed_urls_key_file_path_description,
    		textName: "signed-urls-key-file-path",
    		placeholder: /*$delivery_provider*/ ctx[1].signed_urls_key_file_path_placeholder,
    		nested: true
    	};

    	if (/*$settings*/ ctx[2]["signed-urls-key-file-path"] !== void 0) {
    		settingspaneloption1_props.text = /*$settings*/ ctx[2]["signed-urls-key-file-path"];
    	}

    	settingspaneloption1 = new SettingsPanelOption({
    			props: settingspaneloption1_props,
    			$$inline: true
    		});

    	binding_callbacks.push(() => bind(settingspaneloption1, 'text', settingspaneloption1_text_binding));

    	function settingspaneloption2_text_binding(value) {
    		/*settingspaneloption2_text_binding*/ ctx[9](value);
    	}

    	let settingspaneloption2_props = {
    		heading: /*$delivery_provider*/ ctx[1].signed_urls_object_prefix_name,
    		description: /*$delivery_provider*/ ctx[1].signed_urls_object_prefix_description,
    		textName: "signed-urls-object-prefix",
    		placeholder: "private/",
    		nested: true
    	};

    	if (/*$settings*/ ctx[2]["signed-urls-object-prefix"] !== void 0) {
    		settingspaneloption2_props.text = /*$settings*/ ctx[2]["signed-urls-object-prefix"];
    	}

    	settingspaneloption2 = new SettingsPanelOption({
    			props: settingspaneloption2_props,
    			$$inline: true
    		});

    	binding_callbacks.push(() => bind(settingspaneloption2, 'text', settingspaneloption2_text_binding));

    	const block = {
    		c: function create() {
    			create_component(settingspaneloption0.$$.fragment);
    			t0 = space();
    			create_component(settingspaneloption1.$$.fragment);
    			t1 = space();
    			create_component(settingspaneloption2.$$.fragment);
    		},
    		m: function mount(target, anchor) {
    			mount_component(settingspaneloption0, target, anchor);
    			insert_dev(target, t0, anchor);
    			mount_component(settingspaneloption1, target, anchor);
    			insert_dev(target, t1, anchor);
    			mount_component(settingspaneloption2, target, anchor);
    			current = true;
    		},
    		p: function update(ctx, dirty) {
    			const settingspaneloption0_changes = {};
    			if (dirty & /*$delivery_provider*/ 2) settingspaneloption0_changes.heading = /*$delivery_provider*/ ctx[1].signed_urls_key_id_name;
    			if (dirty & /*$delivery_provider*/ 2) settingspaneloption0_changes.description = /*$delivery_provider*/ ctx[1].signed_urls_key_id_description;

    			if (!updating_text && dirty & /*$settings*/ 4) {
    				updating_text = true;
    				settingspaneloption0_changes.text = /*$settings*/ ctx[2]["signed-urls-key-id"];
    				add_flush_callback(() => updating_text = false);
    			}

    			settingspaneloption0.$set(settingspaneloption0_changes);
    			const settingspaneloption1_changes = {};
    			if (dirty & /*$delivery_provider*/ 2) settingspaneloption1_changes.heading = /*$delivery_provider*/ ctx[1].signed_urls_key_file_path_name;
    			if (dirty & /*$delivery_provider*/ 2) settingspaneloption1_changes.description = /*$delivery_provider*/ ctx[1].signed_urls_key_file_path_description;
    			if (dirty & /*$delivery_provider*/ 2) settingspaneloption1_changes.placeholder = /*$delivery_provider*/ ctx[1].signed_urls_key_file_path_placeholder;

    			if (!updating_text_1 && dirty & /*$settings*/ 4) {
    				updating_text_1 = true;
    				settingspaneloption1_changes.text = /*$settings*/ ctx[2]["signed-urls-key-file-path"];
    				add_flush_callback(() => updating_text_1 = false);
    			}

    			settingspaneloption1.$set(settingspaneloption1_changes);
    			const settingspaneloption2_changes = {};
    			if (dirty & /*$delivery_provider*/ 2) settingspaneloption2_changes.heading = /*$delivery_provider*/ ctx[1].signed_urls_object_prefix_name;
    			if (dirty & /*$delivery_provider*/ 2) settingspaneloption2_changes.description = /*$delivery_provider*/ ctx[1].signed_urls_object_prefix_description;

    			if (!updating_text_2 && dirty & /*$settings*/ 4) {
    				updating_text_2 = true;
    				settingspaneloption2_changes.text = /*$settings*/ ctx[2]["signed-urls-object-prefix"];
    				add_flush_callback(() => updating_text_2 = false);
    			}

    			settingspaneloption2.$set(settingspaneloption2_changes);
    		},
    		i: function intro(local) {
    			if (current) return;
    			transition_in(settingspaneloption0.$$.fragment, local);
    			transition_in(settingspaneloption1.$$.fragment, local);
    			transition_in(settingspaneloption2.$$.fragment, local);
    			current = true;
    		},
    		o: function outro(local) {
    			transition_out(settingspaneloption0.$$.fragment, local);
    			transition_out(settingspaneloption1.$$.fragment, local);
    			transition_out(settingspaneloption2.$$.fragment, local);
    			current = false;
    		},
    		d: function destroy(detaching) {
    			destroy_component(settingspaneloption0, detaching);
    			if (detaching) detach_dev(t0);
    			destroy_component(settingspaneloption1, detaching);
    			if (detaching) detach_dev(t1);
    			destroy_component(settingspaneloption2, detaching);
    		}
    	};

    	dispatch_dev("SvelteRegisterBlock", {
    		block,
    		id: create_if_block_2$4.name,
    		type: "if",
    		source: "(59:4) {#if $settings[ \\\"enable-signed-urls\\\" ]}",
    		ctx
    	});

    	return block;
    }

    // (52:3) <SettingsPanelOption     heading={$delivery_provider.signed_urls_option_name}     description={$delivery_provider.signed_urls_option_description}     toggleName="enable-signed-urls"     bind:toggle={$settings["enable-signed-urls"]}    >
    function create_default_slot_1$7(ctx) {
    	let if_block_anchor;
    	let current;
    	let if_block = /*$settings*/ ctx[2]["enable-signed-urls"] && create_if_block_2$4(ctx);

    	const block = {
    		c: function create() {
    			if (if_block) if_block.c();
    			if_block_anchor = empty();
    		},
    		m: function mount(target, anchor) {
    			if (if_block) if_block.m(target, anchor);
    			insert_dev(target, if_block_anchor, anchor);
    			current = true;
    		},
    		p: function update(ctx, dirty) {
    			if (/*$settings*/ ctx[2]["enable-signed-urls"]) {
    				if (if_block) {
    					if_block.p(ctx, dirty);

    					if (dirty & /*$settings*/ 4) {
    						transition_in(if_block, 1);
    					}
    				} else {
    					if_block = create_if_block_2$4(ctx);
    					if_block.c();
    					transition_in(if_block, 1);
    					if_block.m(if_block_anchor.parentNode, if_block_anchor);
    				}
    			} else if (if_block) {
    				group_outros();

    				transition_out(if_block, 1, 1, () => {
    					if_block = null;
    				});

    				check_outros();
    			}
    		},
    		i: function intro(local) {
    			if (current) return;
    			transition_in(if_block);
    			current = true;
    		},
    		o: function outro(local) {
    			transition_out(if_block);
    			current = false;
    		},
    		d: function destroy(detaching) {
    			if (if_block) if_block.d(detaching);
    			if (detaching) detach_dev(if_block_anchor);
    		}
    	};

    	dispatch_dev("SvelteRegisterBlock", {
    		block,
    		id: create_default_slot_1$7.name,
    		type: "slot",
    		source: "(52:3) <SettingsPanelOption     heading={$delivery_provider.signed_urls_option_name}     description={$delivery_provider.signed_urls_option_description}     toggleName=\\\"enable-signed-urls\\\"     bind:toggle={$settings[\\\"enable-signed-urls\\\"]}    >",
    		ctx
    	});

    	return block;
    }

    // (32:0) <Panel name="settings" heading={$strings.delivery_settings_title} helpKey="delivery-provider">
    function create_default_slot$g(ctx) {
    	let deliverysettingsheadingrow;
    	let t0;
    	let settingspaneloption0;
    	let updating_toggle;
    	let t1;
    	let t2;
    	let settingspaneloption1;
    	let updating_toggle_1;
    	let current;
    	deliverysettingsheadingrow = new DeliverySettingsHeadingRow({ $$inline: true });

    	function settingspaneloption0_toggle_binding(value) {
    		/*settingspaneloption0_toggle_binding*/ ctx[4](value);
    	}

    	let settingspaneloption0_props = {
    		heading: /*$strings*/ ctx[0].rewrite_media_urls,
    		description: /*$delivery_provider*/ ctx[1].rewrite_media_urls_desc,
    		toggleName: "serve-from-s3"
    	};

    	if (/*$settings*/ ctx[2]["serve-from-s3"] !== void 0) {
    		settingspaneloption0_props.toggle = /*$settings*/ ctx[2]["serve-from-s3"];
    	}

    	settingspaneloption0 = new SettingsPanelOption({
    			props: settingspaneloption0_props,
    			$$inline: true
    		});

    	binding_callbacks.push(() => bind(settingspaneloption0, 'toggle', settingspaneloption0_toggle_binding));
    	let if_block = /*$delivery_provider*/ ctx[1].delivery_domain_allowed && create_if_block$d(ctx);

    	function settingspaneloption1_toggle_binding(value) {
    		/*settingspaneloption1_toggle_binding*/ ctx[11](value);
    	}

    	let settingspaneloption1_props = {
    		heading: /*$strings*/ ctx[0].force_https,
    		description: /*$strings*/ ctx[0].force_https_desc,
    		toggleName: "force-https"
    	};

    	if (/*$settings*/ ctx[2]["force-https"] !== void 0) {
    		settingspaneloption1_props.toggle = /*$settings*/ ctx[2]["force-https"];
    	}

    	settingspaneloption1 = new SettingsPanelOption({
    			props: settingspaneloption1_props,
    			$$inline: true
    		});

    	binding_callbacks.push(() => bind(settingspaneloption1, 'toggle', settingspaneloption1_toggle_binding));

    	const block = {
    		c: function create() {
    			create_component(deliverysettingsheadingrow.$$.fragment);
    			t0 = space();
    			create_component(settingspaneloption0.$$.fragment);
    			t1 = space();
    			if (if_block) if_block.c();
    			t2 = space();
    			create_component(settingspaneloption1.$$.fragment);
    		},
    		m: function mount(target, anchor) {
    			mount_component(deliverysettingsheadingrow, target, anchor);
    			insert_dev(target, t0, anchor);
    			mount_component(settingspaneloption0, target, anchor);
    			insert_dev(target, t1, anchor);
    			if (if_block) if_block.m(target, anchor);
    			insert_dev(target, t2, anchor);
    			mount_component(settingspaneloption1, target, anchor);
    			current = true;
    		},
    		p: function update(ctx, dirty) {
    			const settingspaneloption0_changes = {};
    			if (dirty & /*$strings*/ 1) settingspaneloption0_changes.heading = /*$strings*/ ctx[0].rewrite_media_urls;
    			if (dirty & /*$delivery_provider*/ 2) settingspaneloption0_changes.description = /*$delivery_provider*/ ctx[1].rewrite_media_urls_desc;

    			if (!updating_toggle && dirty & /*$settings*/ 4) {
    				updating_toggle = true;
    				settingspaneloption0_changes.toggle = /*$settings*/ ctx[2]["serve-from-s3"];
    				add_flush_callback(() => updating_toggle = false);
    			}

    			settingspaneloption0.$set(settingspaneloption0_changes);

    			if (/*$delivery_provider*/ ctx[1].delivery_domain_allowed) {
    				if (if_block) {
    					if_block.p(ctx, dirty);

    					if (dirty & /*$delivery_provider*/ 2) {
    						transition_in(if_block, 1);
    					}
    				} else {
    					if_block = create_if_block$d(ctx);
    					if_block.c();
    					transition_in(if_block, 1);
    					if_block.m(t2.parentNode, t2);
    				}
    			} else if (if_block) {
    				group_outros();

    				transition_out(if_block, 1, 1, () => {
    					if_block = null;
    				});

    				check_outros();
    			}

    			const settingspaneloption1_changes = {};
    			if (dirty & /*$strings*/ 1) settingspaneloption1_changes.heading = /*$strings*/ ctx[0].force_https;
    			if (dirty & /*$strings*/ 1) settingspaneloption1_changes.description = /*$strings*/ ctx[0].force_https_desc;

    			if (!updating_toggle_1 && dirty & /*$settings*/ 4) {
    				updating_toggle_1 = true;
    				settingspaneloption1_changes.toggle = /*$settings*/ ctx[2]["force-https"];
    				add_flush_callback(() => updating_toggle_1 = false);
    			}

    			settingspaneloption1.$set(settingspaneloption1_changes);
    		},
    		i: function intro(local) {
    			if (current) return;
    			transition_in(deliverysettingsheadingrow.$$.fragment, local);
    			transition_in(settingspaneloption0.$$.fragment, local);
    			transition_in(if_block);
    			transition_in(settingspaneloption1.$$.fragment, local);
    			current = true;
    		},
    		o: function outro(local) {
    			transition_out(deliverysettingsheadingrow.$$.fragment, local);
    			transition_out(settingspaneloption0.$$.fragment, local);
    			transition_out(if_block);
    			transition_out(settingspaneloption1.$$.fragment, local);
    			current = false;
    		},
    		d: function destroy(detaching) {
    			destroy_component(deliverysettingsheadingrow, detaching);
    			if (detaching) detach_dev(t0);
    			destroy_component(settingspaneloption0, detaching);
    			if (detaching) detach_dev(t1);
    			if (if_block) if_block.d(detaching);
    			if (detaching) detach_dev(t2);
    			destroy_component(settingspaneloption1, detaching);
    		}
    	};

    	dispatch_dev("SvelteRegisterBlock", {
    		block,
    		id: create_default_slot$g.name,
    		type: "slot",
    		source: "(32:0) <Panel name=\\\"settings\\\" heading={$strings.delivery_settings_title} helpKey=\\\"delivery-provider\\\">",
    		ctx
    	});

    	return block;
    }

    function create_fragment$y(ctx) {
    	let panel;
    	let current;

    	panel = new Panel({
    			props: {
    				name: "settings",
    				heading: /*$strings*/ ctx[0].delivery_settings_title,
    				helpKey: "delivery-provider",
    				$$slots: { default: [create_default_slot$g] },
    				$$scope: { ctx }
    			},
    			$$inline: true
    		});

    	const block = {
    		c: function create() {
    			create_component(panel.$$.fragment);
    		},
    		l: function claim(nodes) {
    			throw new Error("options.hydrate only works if the component was compiled with the `hydratable: true` option");
    		},
    		m: function mount(target, anchor) {
    			mount_component(panel, target, anchor);
    			current = true;
    		},
    		p: function update(ctx, [dirty]) {
    			const panel_changes = {};
    			if (dirty & /*$strings*/ 1) panel_changes.heading = /*$strings*/ ctx[0].delivery_settings_title;

    			if (dirty & /*$$scope, $strings, $settings, $delivery_provider*/ 4103) {
    				panel_changes.$$scope = { dirty, ctx };
    			}

    			panel.$set(panel_changes);
    		},
    		i: function intro(local) {
    			if (current) return;
    			transition_in(panel.$$.fragment, local);
    			current = true;
    		},
    		o: function outro(local) {
    			transition_out(panel.$$.fragment, local);
    			current = false;
    		},
    		d: function destroy(detaching) {
    			destroy_component(panel, detaching);
    		}
    	};

    	dispatch_dev("SvelteRegisterBlock", {
    		block,
    		id: create_fragment$y.name,
    		type: "component",
    		source: "",
    		ctx
    	});

    	return block;
    }

    function instance$y($$self, $$props, $$invalidate) {
    	let $strings;
    	let $delivery_provider;
    	let $settings;
    	validate_store(strings, 'strings');
    	component_subscribe($$self, strings, $$value => $$invalidate(0, $strings = $$value));
    	validate_store(delivery_provider, 'delivery_provider');
    	component_subscribe($$self, delivery_provider, $$value => $$invalidate(1, $delivery_provider = $$value));
    	validate_store(settings, 'settings');
    	component_subscribe($$self, settings, $$value => $$invalidate(2, $settings = $$value));
    	let { $$slots: slots = {}, $$scope } = $$props;
    	validate_slots('DeliverySettingsPanel', slots, []);

    	function domainValidator(domain) {
    		const domainPattern = /[^a-z0-9.-]/;
    		let message = "";

    		if (domain.trim().length === 0) {
    			message = $strings.domain_blank;
    		} else if (true === domainPattern.test(domain)) {
    			message = $strings.domain_invalid_content;
    		} else if (domain.length < 3) {
    			message = $strings.domain_too_short;
    		}

    		return message;
    	}

    	const writable_props = [];

    	Object.keys($$props).forEach(key => {
    		if (!~writable_props.indexOf(key) && key.slice(0, 2) !== '$$' && key !== 'slot') console.warn(`<DeliverySettingsPanel> was created with unknown prop '${key}'`);
    	});

    	function settingspaneloption0_toggle_binding(value) {
    		if ($$self.$$.not_equal($settings["serve-from-s3"], value)) {
    			$settings["serve-from-s3"] = value;
    			settings.set($settings);
    		}
    	}

    	function settingspaneloption_toggle_binding(value) {
    		if ($$self.$$.not_equal($settings["enable-delivery-domain"], value)) {
    			$settings["enable-delivery-domain"] = value;
    			settings.set($settings);
    		}
    	}

    	function settingspaneloption_text_binding(value) {
    		if ($$self.$$.not_equal($settings["delivery-domain"], value)) {
    			$settings["delivery-domain"] = value;
    			settings.set($settings);
    		}
    	}

    	function settingspaneloption0_text_binding(value) {
    		if ($$self.$$.not_equal($settings["signed-urls-key-id"], value)) {
    			$settings["signed-urls-key-id"] = value;
    			settings.set($settings);
    		}
    	}

    	function settingspaneloption1_text_binding(value) {
    		if ($$self.$$.not_equal($settings["signed-urls-key-file-path"], value)) {
    			$settings["signed-urls-key-file-path"] = value;
    			settings.set($settings);
    		}
    	}

    	function settingspaneloption2_text_binding(value) {
    		if ($$self.$$.not_equal($settings["signed-urls-object-prefix"], value)) {
    			$settings["signed-urls-object-prefix"] = value;
    			settings.set($settings);
    		}
    	}

    	function settingspaneloption_toggle_binding_1(value) {
    		if ($$self.$$.not_equal($settings["enable-signed-urls"], value)) {
    			$settings["enable-signed-urls"] = value;
    			settings.set($settings);
    		}
    	}

    	function settingspaneloption1_toggle_binding(value) {
    		if ($$self.$$.not_equal($settings["force-https"], value)) {
    			$settings["force-https"] = value;
    			settings.set($settings);
    		}
    	}

    	$$self.$capture_state = () => ({
    		delivery_provider,
    		settings,
    		strings,
    		Panel,
    		DeliverySettingsHeadingRow,
    		SettingsPanelOption,
    		domainValidator,
    		$strings,
    		$delivery_provider,
    		$settings
    	});

    	return [
    		$strings,
    		$delivery_provider,
    		$settings,
    		domainValidator,
    		settingspaneloption0_toggle_binding,
    		settingspaneloption_toggle_binding,
    		settingspaneloption_text_binding,
    		settingspaneloption0_text_binding,
    		settingspaneloption1_text_binding,
    		settingspaneloption2_text_binding,
    		settingspaneloption_toggle_binding_1,
    		settingspaneloption1_toggle_binding
    	];
    }

    class DeliverySettingsPanel extends SvelteComponentDev {
    	constructor(options) {
    		super(options);
    		init(this, options, instance$y, create_fragment$y, safe_not_equal, {});

    		dispatch_dev("SvelteRegisterComponent", {
    			component: this,
    			tagName: "DeliverySettingsPanel",
    			options,
    			id: create_fragment$y.name
    		});
    	}
    }

    /* src/amazon-s3-and-cloudfront/ui/components/DeliverySettingsSubPage.svelte generated by Svelte v3.49.0 */

    // (6:0) <SubPage name="delivery-settings" route="/media/delivery">
    function create_default_slot$f(ctx) {
    	let deliverysettingspanel;
    	let current;
    	deliverysettingspanel = new DeliverySettingsPanel({ $$inline: true });

    	const block = {
    		c: function create() {
    			create_component(deliverysettingspanel.$$.fragment);
    		},
    		m: function mount(target, anchor) {
    			mount_component(deliverysettingspanel, target, anchor);
    			current = true;
    		},
    		i: function intro(local) {
    			if (current) return;
    			transition_in(deliverysettingspanel.$$.fragment, local);
    			current = true;
    		},
    		o: function outro(local) {
    			transition_out(deliverysettingspanel.$$.fragment, local);
    			current = false;
    		},
    		d: function destroy(detaching) {
    			destroy_component(deliverysettingspanel, detaching);
    		}
    	};

    	dispatch_dev("SvelteRegisterBlock", {
    		block,
    		id: create_default_slot$f.name,
    		type: "slot",
    		source: "(6:0) <SubPage name=\\\"delivery-settings\\\" route=\\\"/media/delivery\\\">",
    		ctx
    	});

    	return block;
    }

    function create_fragment$x(ctx) {
    	let subpage;
    	let current;

    	subpage = new SubPage({
    			props: {
    				name: "delivery-settings",
    				route: "/media/delivery",
    				$$slots: { default: [create_default_slot$f] },
    				$$scope: { ctx }
    			},
    			$$inline: true
    		});

    	const block = {
    		c: function create() {
    			create_component(subpage.$$.fragment);
    		},
    		l: function claim(nodes) {
    			throw new Error("options.hydrate only works if the component was compiled with the `hydratable: true` option");
    		},
    		m: function mount(target, anchor) {
    			mount_component(subpage, target, anchor);
    			current = true;
    		},
    		p: function update(ctx, [dirty]) {
    			const subpage_changes = {};

    			if (dirty & /*$$scope*/ 1) {
    				subpage_changes.$$scope = { dirty, ctx };
    			}

    			subpage.$set(subpage_changes);
    		},
    		i: function intro(local) {
    			if (current) return;
    			transition_in(subpage.$$.fragment, local);
    			current = true;
    		},
    		o: function outro(local) {
    			transition_out(subpage.$$.fragment, local);
    			current = false;
    		},
    		d: function destroy(detaching) {
    			destroy_component(subpage, detaching);
    		}
    	};

    	dispatch_dev("SvelteRegisterBlock", {
    		block,
    		id: create_fragment$x.name,
    		type: "component",
    		source: "",
    		ctx
    	});

    	return block;
    }

    function instance$x($$self, $$props, $$invalidate) {
    	let { $$slots: slots = {}, $$scope } = $$props;
    	validate_slots('DeliverySettingsSubPage', slots, []);
    	const writable_props = [];

    	Object.keys($$props).forEach(key => {
    		if (!~writable_props.indexOf(key) && key.slice(0, 2) !== '$$' && key !== 'slot') console.warn(`<DeliverySettingsSubPage> was created with unknown prop '${key}'`);
    	});

    	$$self.$capture_state = () => ({ SubPage, DeliverySettingsPanel });
    	return [];
    }

    class DeliverySettingsSubPage extends SvelteComponentDev {
    	constructor(options) {
    		super(options);
    		init(this, options, instance$x, create_fragment$x, safe_not_equal, {});

    		dispatch_dev("SvelteRegisterComponent", {
    			component: this,
    			tagName: "DeliverySettingsSubPage",
    			options,
    			id: create_fragment$x.name
    		});
    	}
    }

    /* src/amazon-s3-and-cloudfront/ui/components/MediaSettings.svelte generated by Svelte v3.49.0 */

    function create_fragment$w(ctx) {
    	let storagesettingssubpage;
    	let t;
    	let deliverysettingssubpage;
    	let current;
    	storagesettingssubpage = new StorageSettingsSubPage({ $$inline: true });
    	deliverysettingssubpage = new DeliverySettingsSubPage({ $$inline: true });

    	const block = {
    		c: function create() {
    			create_component(storagesettingssubpage.$$.fragment);
    			t = space();
    			create_component(deliverysettingssubpage.$$.fragment);
    		},
    		l: function claim(nodes) {
    			throw new Error("options.hydrate only works if the component was compiled with the `hydratable: true` option");
    		},
    		m: function mount(target, anchor) {
    			mount_component(storagesettingssubpage, target, anchor);
    			insert_dev(target, t, anchor);
    			mount_component(deliverysettingssubpage, target, anchor);
    			current = true;
    		},
    		p: noop,
    		i: function intro(local) {
    			if (current) return;
    			transition_in(storagesettingssubpage.$$.fragment, local);
    			transition_in(deliverysettingssubpage.$$.fragment, local);
    			current = true;
    		},
    		o: function outro(local) {
    			transition_out(storagesettingssubpage.$$.fragment, local);
    			transition_out(deliverysettingssubpage.$$.fragment, local);
    			current = false;
    		},
    		d: function destroy(detaching) {
    			destroy_component(storagesettingssubpage, detaching);
    			if (detaching) detach_dev(t);
    			destroy_component(deliverysettingssubpage, detaching);
    		}
    	};

    	dispatch_dev("SvelteRegisterBlock", {
    		block,
    		id: create_fragment$w.name,
    		type: "component",
    		source: "",
    		ctx
    	});

    	return block;
    }

    function instance$w($$self, $$props, $$invalidate) {
    	let { $$slots: slots = {}, $$scope } = $$props;
    	validate_slots('MediaSettings', slots, []);
    	let { params = {} } = $$props;
    	const _params = params; // Stops compiler warning about unused params export;
    	const writable_props = ['params'];

    	Object.keys($$props).forEach(key => {
    		if (!~writable_props.indexOf(key) && key.slice(0, 2) !== '$$' && key !== 'slot') console.warn(`<MediaSettings> was created with unknown prop '${key}'`);
    	});

    	$$self.$$set = $$props => {
    		if ('params' in $$props) $$invalidate(0, params = $$props.params);
    	};

    	$$self.$capture_state = () => ({
    		StorageSettingsSubPage,
    		DeliverySettingsSubPage,
    		params,
    		_params
    	});

    	$$self.$inject_state = $$props => {
    		if ('params' in $$props) $$invalidate(0, params = $$props.params);
    	};

    	if ($$props && "$$inject" in $$props) {
    		$$self.$inject_state($$props.$$inject);
    	}

    	return [params];
    }

    class MediaSettings extends SvelteComponentDev {
    	constructor(options) {
    		super(options);
    		init(this, options, instance$w, create_fragment$w, safe_not_equal, { params: 0 });

    		dispatch_dev("SvelteRegisterComponent", {
    			component: this,
    			tagName: "MediaSettings",
    			options,
    			id: create_fragment$w.name
    		});
    	}

    	get params() {
    		throw new Error("<MediaSettings>: Props cannot be read directly from the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
    	}

    	set params(value) {
    		throw new Error("<MediaSettings>: Props cannot be set directly on the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
    	}
    }

    /* src/amazon-s3-and-cloudfront/ui/components/UrlPreview.svelte generated by Svelte v3.49.0 */
    const file$t = "src/amazon-s3-and-cloudfront/ui/components/UrlPreview.svelte";

    function get_each_context$5(ctx, list, i) {
    	const child_ctx = ctx.slice();
    	child_ctx[7] = list[i];
    	return child_ctx;
    }

    // (43:0) {#if parts.length > 0}
    function create_if_block$c(ctx) {
    	let panel;
    	let current;

    	panel = new Panel({
    			props: {
    				name: "url-preview",
    				heading: /*$strings*/ ctx[1].url_preview_title,
    				$$slots: { default: [create_default_slot$e] },
    				$$scope: { ctx }
    			},
    			$$inline: true
    		});

    	const block = {
    		c: function create() {
    			create_component(panel.$$.fragment);
    		},
    		m: function mount(target, anchor) {
    			mount_component(panel, target, anchor);
    			current = true;
    		},
    		p: function update(ctx, dirty) {
    			const panel_changes = {};
    			if (dirty & /*$strings*/ 2) panel_changes.heading = /*$strings*/ ctx[1].url_preview_title;

    			if (dirty & /*$$scope, parts, $strings*/ 1027) {
    				panel_changes.$$scope = { dirty, ctx };
    			}

    			panel.$set(panel_changes);
    		},
    		i: function intro(local) {
    			if (current) return;
    			transition_in(panel.$$.fragment, local);
    			current = true;
    		},
    		o: function outro(local) {
    			transition_out(panel.$$.fragment, local);
    			current = false;
    		},
    		d: function destroy(detaching) {
    			destroy_component(panel, detaching);
    		}
    	};

    	dispatch_dev("SvelteRegisterBlock", {
    		block,
    		id: create_if_block$c.name,
    		type: "if",
    		source: "(43:0) {#if parts.length > 0}",
    		ctx
    	});

    	return block;
    }

    // (45:2) <PanelRow class="desc">
    function create_default_slot_2$5(ctx) {
    	let p;
    	let t_value = /*$strings*/ ctx[1].url_preview_desc + "";
    	let t;

    	const block = {
    		c: function create() {
    			p = element("p");
    			t = text(t_value);
    			add_location(p, file$t, 45, 3, 1186);
    		},
    		m: function mount(target, anchor) {
    			insert_dev(target, p, anchor);
    			append_dev(p, t);
    		},
    		p: function update(ctx, dirty) {
    			if (dirty & /*$strings*/ 2 && t_value !== (t_value = /*$strings*/ ctx[1].url_preview_desc + "")) set_data_dev(t, t_value);
    		},
    		d: function destroy(detaching) {
    			if (detaching) detach_dev(p);
    		}
    	};

    	dispatch_dev("SvelteRegisterBlock", {
    		block,
    		id: create_default_slot_2$5.name,
    		type: "slot",
    		source: "(45:2) <PanelRow class=\\\"desc\\\">",
    		ctx
    	});

    	return block;
    }

    // (50:4) {#each parts as part (part.title)}
    function create_each_block$5(key_1, ctx) {
    	let div;
    	let dt;
    	let t0_value = /*part*/ ctx[7].title + "";
    	let t0;
    	let t1;
    	let dd;
    	let t2_value = /*part*/ ctx[7].example + "";
    	let t2;
    	let t3;
    	let div_data_key_value;
    	let div_transition;
    	let current;

    	const block = {
    		key: key_1,
    		first: null,
    		c: function create() {
    			div = element("div");
    			dt = element("dt");
    			t0 = text(t0_value);
    			t1 = space();
    			dd = element("dd");
    			t2 = text(t2_value);
    			t3 = space();
    			add_location(dt, file$t, 51, 6, 1377);
    			add_location(dd, file$t, 52, 6, 1405);
    			attr_dev(div, "data-key", div_data_key_value = /*part*/ ctx[7].key);
    			add_location(div, file$t, 50, 5, 1322);
    			this.first = div;
    		},
    		m: function mount(target, anchor) {
    			insert_dev(target, div, anchor);
    			append_dev(div, dt);
    			append_dev(dt, t0);
    			append_dev(div, t1);
    			append_dev(div, dd);
    			append_dev(dd, t2);
    			append_dev(div, t3);
    			current = true;
    		},
    		p: function update(new_ctx, dirty) {
    			ctx = new_ctx;
    			if ((!current || dirty & /*parts*/ 1) && t0_value !== (t0_value = /*part*/ ctx[7].title + "")) set_data_dev(t0, t0_value);
    			if ((!current || dirty & /*parts*/ 1) && t2_value !== (t2_value = /*part*/ ctx[7].example + "")) set_data_dev(t2, t2_value);

    			if (!current || dirty & /*parts*/ 1 && div_data_key_value !== (div_data_key_value = /*part*/ ctx[7].key)) {
    				attr_dev(div, "data-key", div_data_key_value);
    			}
    		},
    		i: function intro(local) {
    			if (current) return;

    			if (local) {
    				add_render_callback(() => {
    					if (!div_transition) div_transition = create_bidirectional_transition(div, scale, {}, true);
    					div_transition.run(1);
    				});
    			}

    			current = true;
    		},
    		o: function outro(local) {
    			if (local) {
    				if (!div_transition) div_transition = create_bidirectional_transition(div, scale, {}, false);
    				div_transition.run(0);
    			}

    			current = false;
    		},
    		d: function destroy(detaching) {
    			if (detaching) detach_dev(div);
    			if (detaching && div_transition) div_transition.end();
    		}
    	};

    	dispatch_dev("SvelteRegisterBlock", {
    		block,
    		id: create_each_block$5.name,
    		type: "each",
    		source: "(50:4) {#each parts as part (part.title)}",
    		ctx
    	});

    	return block;
    }

    // (48:2) <PanelRow class="body flex-row">
    function create_default_slot_1$6(ctx) {
    	let dl;
    	let each_blocks = [];
    	let each_1_lookup = new Map();
    	let current;
    	let each_value = /*parts*/ ctx[0];
    	validate_each_argument(each_value);
    	const get_key = ctx => /*part*/ ctx[7].title;
    	validate_each_keys(ctx, each_value, get_each_context$5, get_key);

    	for (let i = 0; i < each_value.length; i += 1) {
    		let child_ctx = get_each_context$5(ctx, each_value, i);
    		let key = get_key(child_ctx);
    		each_1_lookup.set(key, each_blocks[i] = create_each_block$5(key, child_ctx));
    	}

    	const block = {
    		c: function create() {
    			dl = element("dl");

    			for (let i = 0; i < each_blocks.length; i += 1) {
    				each_blocks[i].c();
    			}

    			add_location(dl, file$t, 48, 3, 1273);
    		},
    		m: function mount(target, anchor) {
    			insert_dev(target, dl, anchor);

    			for (let i = 0; i < each_blocks.length; i += 1) {
    				each_blocks[i].m(dl, null);
    			}

    			current = true;
    		},
    		p: function update(ctx, dirty) {
    			if (dirty & /*parts*/ 1) {
    				each_value = /*parts*/ ctx[0];
    				validate_each_argument(each_value);
    				group_outros();
    				validate_each_keys(ctx, each_value, get_each_context$5, get_key);
    				each_blocks = update_keyed_each(each_blocks, dirty, get_key, 1, ctx, each_value, each_1_lookup, dl, outro_and_destroy_block, create_each_block$5, null, get_each_context$5);
    				check_outros();
    			}
    		},
    		i: function intro(local) {
    			if (current) return;

    			for (let i = 0; i < each_value.length; i += 1) {
    				transition_in(each_blocks[i]);
    			}

    			current = true;
    		},
    		o: function outro(local) {
    			for (let i = 0; i < each_blocks.length; i += 1) {
    				transition_out(each_blocks[i]);
    			}

    			current = false;
    		},
    		d: function destroy(detaching) {
    			if (detaching) detach_dev(dl);

    			for (let i = 0; i < each_blocks.length; i += 1) {
    				each_blocks[i].d();
    			}
    		}
    	};

    	dispatch_dev("SvelteRegisterBlock", {
    		block,
    		id: create_default_slot_1$6.name,
    		type: "slot",
    		source: "(48:2) <PanelRow class=\\\"body flex-row\\\">",
    		ctx
    	});

    	return block;
    }

    // (44:1) <Panel name="url-preview" heading={$strings.url_preview_title}>
    function create_default_slot$e(ctx) {
    	let panelrow0;
    	let t;
    	let panelrow1;
    	let current;

    	panelrow0 = new PanelRow({
    			props: {
    				class: "desc",
    				$$slots: { default: [create_default_slot_2$5] },
    				$$scope: { ctx }
    			},
    			$$inline: true
    		});

    	panelrow1 = new PanelRow({
    			props: {
    				class: "body flex-row",
    				$$slots: { default: [create_default_slot_1$6] },
    				$$scope: { ctx }
    			},
    			$$inline: true
    		});

    	const block = {
    		c: function create() {
    			create_component(panelrow0.$$.fragment);
    			t = space();
    			create_component(panelrow1.$$.fragment);
    		},
    		m: function mount(target, anchor) {
    			mount_component(panelrow0, target, anchor);
    			insert_dev(target, t, anchor);
    			mount_component(panelrow1, target, anchor);
    			current = true;
    		},
    		p: function update(ctx, dirty) {
    			const panelrow0_changes = {};

    			if (dirty & /*$$scope, $strings*/ 1026) {
    				panelrow0_changes.$$scope = { dirty, ctx };
    			}

    			panelrow0.$set(panelrow0_changes);
    			const panelrow1_changes = {};

    			if (dirty & /*$$scope, parts*/ 1025) {
    				panelrow1_changes.$$scope = { dirty, ctx };
    			}

    			panelrow1.$set(panelrow1_changes);
    		},
    		i: function intro(local) {
    			if (current) return;
    			transition_in(panelrow0.$$.fragment, local);
    			transition_in(panelrow1.$$.fragment, local);
    			current = true;
    		},
    		o: function outro(local) {
    			transition_out(panelrow0.$$.fragment, local);
    			transition_out(panelrow1.$$.fragment, local);
    			current = false;
    		},
    		d: function destroy(detaching) {
    			destroy_component(panelrow0, detaching);
    			if (detaching) detach_dev(t);
    			destroy_component(panelrow1, detaching);
    		}
    	};

    	dispatch_dev("SvelteRegisterBlock", {
    		block,
    		id: create_default_slot$e.name,
    		type: "slot",
    		source: "(44:1) <Panel name=\\\"url-preview\\\" heading={$strings.url_preview_title}>",
    		ctx
    	});

    	return block;
    }

    function create_fragment$v(ctx) {
    	let if_block_anchor;
    	let current;
    	let if_block = /*parts*/ ctx[0].length > 0 && create_if_block$c(ctx);

    	const block = {
    		c: function create() {
    			if (if_block) if_block.c();
    			if_block_anchor = empty();
    		},
    		l: function claim(nodes) {
    			throw new Error("options.hydrate only works if the component was compiled with the `hydratable: true` option");
    		},
    		m: function mount(target, anchor) {
    			if (if_block) if_block.m(target, anchor);
    			insert_dev(target, if_block_anchor, anchor);
    			current = true;
    		},
    		p: function update(ctx, [dirty]) {
    			if (/*parts*/ ctx[0].length > 0) {
    				if (if_block) {
    					if_block.p(ctx, dirty);

    					if (dirty & /*parts*/ 1) {
    						transition_in(if_block, 1);
    					}
    				} else {
    					if_block = create_if_block$c(ctx);
    					if_block.c();
    					transition_in(if_block, 1);
    					if_block.m(if_block_anchor.parentNode, if_block_anchor);
    				}
    			} else if (if_block) {
    				group_outros();

    				transition_out(if_block, 1, 1, () => {
    					if_block = null;
    				});

    				check_outros();
    			}
    		},
    		i: function intro(local) {
    			if (current) return;
    			transition_in(if_block);
    			current = true;
    		},
    		o: function outro(local) {
    			transition_out(if_block);
    			current = false;
    		},
    		d: function destroy(detaching) {
    			if (if_block) if_block.d(detaching);
    			if (detaching) detach_dev(if_block_anchor);
    		}
    	};

    	dispatch_dev("SvelteRegisterBlock", {
    		block,
    		id: create_fragment$v.name,
    		type: "component",
    		source: "",
    		ctx
    	});

    	return block;
    }

    function instance$v($$self, $$props, $$invalidate) {
    	let isTemporaryUrl;
    	let $settings;
    	let $settings_changed;
    	let $urls;
    	let $strings;
    	validate_store(settings, 'settings');
    	component_subscribe($$self, settings, $$value => $$invalidate(2, $settings = $$value));
    	validate_store(settings_changed, 'settings_changed');
    	component_subscribe($$self, settings_changed, $$value => $$invalidate(3, $settings_changed = $$value));
    	validate_store(urls, 'urls');
    	component_subscribe($$self, urls, $$value => $$invalidate(4, $urls = $$value));
    	validate_store(strings, 'strings');
    	component_subscribe($$self, strings, $$value => $$invalidate(1, $strings = $$value));
    	let { $$slots: slots = {}, $$scope } = $$props;
    	validate_slots('UrlPreview', slots, []);
    	let parts = $urls.url_parts;

    	/**
     * When settings have changed, show their preview URL, otherwise show saved settings version.
     *
     * Note: This function **assigns** to the `example` and `parts` variables to defeat the reactive demons!
     *
     * @param {Object} urls
     * @param {boolean} settingsChanged
     * @param {Object} settings
     *
     * @returns boolean
     */
    	async function temporaryUrl(urls, settingsChanged, settings) {
    		if (settingsChanged) {
    			const response = await api.post("url-preview", { settings });

    			// Use temporary URLs if available.
    			if (response.hasOwnProperty("url_parts")) {
    				$$invalidate(0, parts = response.url_parts);
    				return true;
    			}
    		}

    		// Reset back to saved URLs.
    		$$invalidate(0, parts = urls.url_parts);

    		return false;
    	}

    	const writable_props = [];

    	Object.keys($$props).forEach(key => {
    		if (!~writable_props.indexOf(key) && key.slice(0, 2) !== '$$' && key !== 'slot') console.warn(`<UrlPreview> was created with unknown prop '${key}'`);
    	});

    	$$self.$capture_state = () => ({
    		scale,
    		api,
    		settings,
    		settings_changed,
    		strings,
    		urls,
    		Panel,
    		PanelRow,
    		parts,
    		temporaryUrl,
    		isTemporaryUrl,
    		$settings,
    		$settings_changed,
    		$urls,
    		$strings
    	});

    	$$self.$inject_state = $$props => {
    		if ('parts' in $$props) $$invalidate(0, parts = $$props.parts);
    		if ('isTemporaryUrl' in $$props) isTemporaryUrl = $$props.isTemporaryUrl;
    	};

    	if ($$props && "$$inject" in $$props) {
    		$$self.$inject_state($$props.$$inject);
    	}

    	$$self.$$.update = () => {
    		if ($$self.$$.dirty & /*$urls, $settings_changed, $settings*/ 28) {
    			isTemporaryUrl = temporaryUrl($urls, $settings_changed, $settings);
    		}
    	};

    	return [parts, $strings, $settings, $settings_changed, $urls];
    }

    class UrlPreview extends SvelteComponentDev {
    	constructor(options) {
    		super(options);
    		init(this, options, instance$v, create_fragment$v, safe_not_equal, {});

    		dispatch_dev("SvelteRegisterComponent", {
    			component: this,
    			tagName: "UrlPreview",
    			options,
    			id: create_fragment$v.name
    		});
    	}
    }

    /**
     * Scrolls the notifications into view.
     */
    function scrollNotificationsIntoView() {
    	const element = document.getElementById( "notifications" );

    	if ( element ) {
    		element.scrollIntoView( { behavior: "smooth", block: "start" } );
    	}
    }

    /* src/amazon-s3-and-cloudfront/ui/components/Footer.svelte generated by Svelte v3.49.0 */
    const file$s = "src/amazon-s3-and-cloudfront/ui/components/Footer.svelte";

    // (61:0) {#if $settingsChangedStore}
    function create_if_block$b(ctx) {
    	let div1;
    	let div0;
    	let button0;
    	let t;
    	let button1;
    	let div1_transition;
    	let current;

    	button0 = new Button({
    			props: {
    				outline: true,
    				$$slots: { default: [create_default_slot_1$5] },
    				$$scope: { ctx }
    			},
    			$$inline: true
    		});

    	button0.$on("click", /*handleCancel*/ ctx[4]);

    	button1 = new Button({
    			props: {
    				primary: true,
    				disabled: /*disabled*/ ctx[1],
    				$$slots: { default: [create_default_slot$d] },
    				$$scope: { ctx }
    			},
    			$$inline: true
    		});

    	button1.$on("click", /*handleSave*/ ctx[5]);

    	const block = {
    		c: function create() {
    			div1 = element("div");
    			div0 = element("div");
    			create_component(button0.$$.fragment);
    			t = space();
    			create_component(button1.$$.fragment);
    			attr_dev(div0, "class", "buttons");
    			add_location(div0, file$s, 62, 2, 1524);
    			attr_dev(div1, "class", "fixed-cta-block");
    			add_location(div1, file$s, 61, 1, 1469);
    		},
    		m: function mount(target, anchor) {
    			insert_dev(target, div1, anchor);
    			append_dev(div1, div0);
    			mount_component(button0, div0, null);
    			append_dev(div0, t);
    			mount_component(button1, div0, null);
    			current = true;
    		},
    		p: function update(ctx, dirty) {
    			const button0_changes = {};

    			if (dirty & /*$$scope, $strings*/ 1032) {
    				button0_changes.$$scope = { dirty, ctx };
    			}

    			button0.$set(button0_changes);
    			const button1_changes = {};
    			if (dirty & /*disabled*/ 2) button1_changes.disabled = /*disabled*/ ctx[1];

    			if (dirty & /*$$scope, $strings*/ 1032) {
    				button1_changes.$$scope = { dirty, ctx };
    			}

    			button1.$set(button1_changes);
    		},
    		i: function intro(local) {
    			if (current) return;
    			transition_in(button0.$$.fragment, local);
    			transition_in(button1.$$.fragment, local);

    			if (local) {
    				add_render_callback(() => {
    					if (!div1_transition) div1_transition = create_bidirectional_transition(div1, slide, {}, true);
    					div1_transition.run(1);
    				});
    			}

    			current = true;
    		},
    		o: function outro(local) {
    			transition_out(button0.$$.fragment, local);
    			transition_out(button1.$$.fragment, local);

    			if (local) {
    				if (!div1_transition) div1_transition = create_bidirectional_transition(div1, slide, {}, false);
    				div1_transition.run(0);
    			}

    			current = false;
    		},
    		d: function destroy(detaching) {
    			if (detaching) detach_dev(div1);
    			destroy_component(button0);
    			destroy_component(button1);
    			if (detaching && div1_transition) div1_transition.end();
    		}
    	};

    	dispatch_dev("SvelteRegisterBlock", {
    		block,
    		id: create_if_block$b.name,
    		type: "if",
    		source: "(61:0) {#if $settingsChangedStore}",
    		ctx
    	});

    	return block;
    }

    // (64:3) <Button outline on:click={handleCancel}>
    function create_default_slot_1$5(ctx) {
    	let t_value = /*$strings*/ ctx[3].cancel_button + "";
    	let t;

    	const block = {
    		c: function create() {
    			t = text(t_value);
    		},
    		m: function mount(target, anchor) {
    			insert_dev(target, t, anchor);
    		},
    		p: function update(ctx, dirty) {
    			if (dirty & /*$strings*/ 8 && t_value !== (t_value = /*$strings*/ ctx[3].cancel_button + "")) set_data_dev(t, t_value);
    		},
    		d: function destroy(detaching) {
    			if (detaching) detach_dev(t);
    		}
    	};

    	dispatch_dev("SvelteRegisterBlock", {
    		block,
    		id: create_default_slot_1$5.name,
    		type: "slot",
    		source: "(64:3) <Button outline on:click={handleCancel}>",
    		ctx
    	});

    	return block;
    }

    // (65:3) <Button primary on:click={handleSave} {disabled}>
    function create_default_slot$d(ctx) {
    	let t_value = /*$strings*/ ctx[3].save_changes + "";
    	let t;

    	const block = {
    		c: function create() {
    			t = text(t_value);
    		},
    		m: function mount(target, anchor) {
    			insert_dev(target, t, anchor);
    		},
    		p: function update(ctx, dirty) {
    			if (dirty & /*$strings*/ 8 && t_value !== (t_value = /*$strings*/ ctx[3].save_changes + "")) set_data_dev(t, t_value);
    		},
    		d: function destroy(detaching) {
    			if (detaching) detach_dev(t);
    		}
    	};

    	dispatch_dev("SvelteRegisterBlock", {
    		block,
    		id: create_default_slot$d.name,
    		type: "slot",
    		source: "(65:3) <Button primary on:click={handleSave} {disabled}>",
    		ctx
    	});

    	return block;
    }

    function create_fragment$u(ctx) {
    	let if_block_anchor;
    	let current;
    	let if_block = /*$settingsChangedStore*/ ctx[2] && create_if_block$b(ctx);

    	const block = {
    		c: function create() {
    			if (if_block) if_block.c();
    			if_block_anchor = empty();
    		},
    		l: function claim(nodes) {
    			throw new Error("options.hydrate only works if the component was compiled with the `hydratable: true` option");
    		},
    		m: function mount(target, anchor) {
    			if (if_block) if_block.m(target, anchor);
    			insert_dev(target, if_block_anchor, anchor);
    			current = true;
    		},
    		p: function update(ctx, [dirty]) {
    			if (/*$settingsChangedStore*/ ctx[2]) {
    				if (if_block) {
    					if_block.p(ctx, dirty);

    					if (dirty & /*$settingsChangedStore*/ 4) {
    						transition_in(if_block, 1);
    					}
    				} else {
    					if_block = create_if_block$b(ctx);
    					if_block.c();
    					transition_in(if_block, 1);
    					if_block.m(if_block_anchor.parentNode, if_block_anchor);
    				}
    			} else if (if_block) {
    				group_outros();

    				transition_out(if_block, 1, 1, () => {
    					if_block = null;
    				});

    				check_outros();
    			}
    		},
    		i: function intro(local) {
    			if (current) return;
    			transition_in(if_block);
    			current = true;
    		},
    		o: function outro(local) {
    			transition_out(if_block);
    			current = false;
    		},
    		d: function destroy(detaching) {
    			if (if_block) if_block.d(detaching);
    			if (detaching) detach_dev(if_block_anchor);
    		}
    	};

    	dispatch_dev("SvelteRegisterBlock", {
    		block,
    		id: create_fragment$u.name,
    		type: "component",
    		source: "",
    		ctx
    	});

    	return block;
    }

    function instance$u($$self, $$props, $$invalidate) {
    	let disabled;
    	let $validationErrors;

    	let $settingsChangedStore,
    		$$unsubscribe_settingsChangedStore = noop,
    		$$subscribe_settingsChangedStore = () => ($$unsubscribe_settingsChangedStore(), $$unsubscribe_settingsChangedStore = subscribe(settingsChangedStore, $$value => $$invalidate(2, $settingsChangedStore = $$value)), settingsChangedStore);

    	let $strings;
    	validate_store(validationErrors, 'validationErrors');
    	component_subscribe($$self, validationErrors, $$value => $$invalidate(8, $validationErrors = $$value));
    	validate_store(strings, 'strings');
    	component_subscribe($$self, strings, $$value => $$invalidate(3, $strings = $$value));
    	$$self.$$.on_destroy.push(() => $$unsubscribe_settingsChangedStore());
    	let { $$slots: slots = {}, $$scope } = $$props;
    	validate_slots('Footer', slots, []);
    	const dispatch = createEventDispatcher();
    	let { settingsStore = settings } = $$props;
    	let { settingsChangedStore = settings_changed } = $$props;
    	validate_store(settingsChangedStore, 'settingsChangedStore');
    	$$subscribe_settingsChangedStore();
    	let saving = false;

    	// On init, start with no validation errors.
    	validationErrors.set(new Map());

    	/**
     * Handles a Cancel button click.
     */
    	function handleCancel() {
    		settingsStore.reset();
    	}

    	/**
     * Handles a Save button click.
     *
     * @return {Promise<void>}
     */
    	async function handleSave() {
    		$$invalidate(7, saving = true);
    		state.pausePeriodicFetch();
    		const result = await settingsStore.save();
    		state.resumePeriodicFetch();

    		// The save happened, whether anything changed or not.
    		if (result.hasOwnProperty("saved") && result.hasOwnProperty("changed_settings")) {
    			dispatch("routeEvent", { event: "settings.save", data: result });
    		}

    		// After save make sure notifications are eyeballed.
    		scrollNotificationsIntoView();

    		$$invalidate(7, saving = false);
    	}

    	// On navigation away from a component showing the footer,
    	// make sure settings are reset.
    	onDestroy(() => handleCancel());

    	const writable_props = ['settingsStore', 'settingsChangedStore'];

    	Object.keys($$props).forEach(key => {
    		if (!~writable_props.indexOf(key) && key.slice(0, 2) !== '$$' && key !== 'slot') console.warn(`<Footer> was created with unknown prop '${key}'`);
    	});

    	$$self.$$set = $$props => {
    		if ('settingsStore' in $$props) $$invalidate(6, settingsStore = $$props.settingsStore);
    		if ('settingsChangedStore' in $$props) $$subscribe_settingsChangedStore($$invalidate(0, settingsChangedStore = $$props.settingsChangedStore));
    	};

    	$$self.$capture_state = () => ({
    		createEventDispatcher,
    		onDestroy,
    		slide,
    		settings_changed,
    		settings,
    		strings,
    		state,
    		validationErrors,
    		scrollNotificationsIntoView,
    		Button,
    		dispatch,
    		settingsStore,
    		settingsChangedStore,
    		saving,
    		handleCancel,
    		handleSave,
    		disabled,
    		$validationErrors,
    		$settingsChangedStore,
    		$strings
    	});

    	$$self.$inject_state = $$props => {
    		if ('settingsStore' in $$props) $$invalidate(6, settingsStore = $$props.settingsStore);
    		if ('settingsChangedStore' in $$props) $$subscribe_settingsChangedStore($$invalidate(0, settingsChangedStore = $$props.settingsChangedStore));
    		if ('saving' in $$props) $$invalidate(7, saving = $$props.saving);
    		if ('disabled' in $$props) $$invalidate(1, disabled = $$props.disabled);
    	};

    	if ($$props && "$$inject" in $$props) {
    		$$self.$inject_state($$props.$$inject);
    	}

    	$$self.$$.update = () => {
    		if ($$self.$$.dirty & /*saving, $validationErrors*/ 384) {
    			$$invalidate(1, disabled = saving || $validationErrors.size > 0);
    		}
    	};

    	return [
    		settingsChangedStore,
    		disabled,
    		$settingsChangedStore,
    		$strings,
    		handleCancel,
    		handleSave,
    		settingsStore,
    		saving,
    		$validationErrors
    	];
    }

    class Footer extends SvelteComponentDev {
    	constructor(options) {
    		super(options);

    		init(this, options, instance$u, create_fragment$u, safe_not_equal, {
    			settingsStore: 6,
    			settingsChangedStore: 0
    		});

    		dispatch_dev("SvelteRegisterComponent", {
    			component: this,
    			tagName: "Footer",
    			options,
    			id: create_fragment$u.name
    		});
    	}

    	get settingsStore() {
    		throw new Error("<Footer>: Props cannot be read directly from the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
    	}

    	set settingsStore(value) {
    		throw new Error("<Footer>: Props cannot be set directly on the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
    	}

    	get settingsChangedStore() {
    		throw new Error("<Footer>: Props cannot be read directly from the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
    	}

    	set settingsChangedStore(value) {
    		throw new Error("<Footer>: Props cannot be set directly on the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
    	}
    }

    /* src/amazon-s3-and-cloudfront/ui/components/MediaPage.svelte generated by Svelte v3.49.0 */
    const file$r = "src/amazon-s3-and-cloudfront/ui/components/MediaPage.svelte";

    // (52:1) {#if render}
    function create_if_block_1$6(ctx) {
    	let notifications;
    	let t0;
    	let div;
    	let blockpublicaccesswarning;
    	let t1;
    	let objectownershipenforcedwarning;
    	let t2;
    	let subnav;
    	let t3;
    	let subpages;
    	let t4;
    	let urlpreview;
    	let current;

    	notifications = new Notifications({
    			props: { tab: /*name*/ ctx[0] },
    			$$inline: true
    		});

    	blockpublicaccesswarning = new BlockPublicAccessWarning({ $$inline: true });
    	objectownershipenforcedwarning = new ObjectOwnershipEnforcedWarning({ $$inline: true });

    	subnav = new SubNav({
    			props: {
    				name: /*name*/ ctx[0],
    				items: /*items*/ ctx[4],
    				subpage: true
    			},
    			$$inline: true
    		});

    	subpages = new SubPages({
    			props: {
    				name: /*name*/ ctx[0],
    				routes: /*routes*/ ctx[3]
    			},
    			$$inline: true
    		});

    	urlpreview = new UrlPreview({ $$inline: true });

    	const block = {
    		c: function create() {
    			create_component(notifications.$$.fragment);
    			t0 = space();
    			div = element("div");
    			create_component(blockpublicaccesswarning.$$.fragment);
    			t1 = space();
    			create_component(objectownershipenforcedwarning.$$.fragment);
    			t2 = space();
    			create_component(subnav.$$.fragment);
    			t3 = space();
    			create_component(subpages.$$.fragment);
    			t4 = space();
    			create_component(urlpreview.$$.fragment);
    			attr_dev(div, "id", "provider-warning-notifications");
    			attr_dev(div, "class", "notifications wrapper");
    			add_location(div, file$r, 53, 2, 1633);
    		},
    		m: function mount(target, anchor) {
    			mount_component(notifications, target, anchor);
    			insert_dev(target, t0, anchor);
    			insert_dev(target, div, anchor);
    			mount_component(blockpublicaccesswarning, div, null);
    			append_dev(div, t1);
    			mount_component(objectownershipenforcedwarning, div, null);
    			insert_dev(target, t2, anchor);
    			mount_component(subnav, target, anchor);
    			insert_dev(target, t3, anchor);
    			mount_component(subpages, target, anchor);
    			insert_dev(target, t4, anchor);
    			mount_component(urlpreview, target, anchor);
    			current = true;
    		},
    		p: function update(ctx, dirty) {
    			const notifications_changes = {};
    			if (dirty & /*name*/ 1) notifications_changes.tab = /*name*/ ctx[0];
    			notifications.$set(notifications_changes);
    			const subnav_changes = {};
    			if (dirty & /*name*/ 1) subnav_changes.name = /*name*/ ctx[0];
    			subnav.$set(subnav_changes);
    			const subpages_changes = {};
    			if (dirty & /*name*/ 1) subpages_changes.name = /*name*/ ctx[0];
    			subpages.$set(subpages_changes);
    		},
    		i: function intro(local) {
    			if (current) return;
    			transition_in(notifications.$$.fragment, local);
    			transition_in(blockpublicaccesswarning.$$.fragment, local);
    			transition_in(objectownershipenforcedwarning.$$.fragment, local);
    			transition_in(subnav.$$.fragment, local);
    			transition_in(subpages.$$.fragment, local);
    			transition_in(urlpreview.$$.fragment, local);
    			current = true;
    		},
    		o: function outro(local) {
    			transition_out(notifications.$$.fragment, local);
    			transition_out(blockpublicaccesswarning.$$.fragment, local);
    			transition_out(objectownershipenforcedwarning.$$.fragment, local);
    			transition_out(subnav.$$.fragment, local);
    			transition_out(subpages.$$.fragment, local);
    			transition_out(urlpreview.$$.fragment, local);
    			current = false;
    		},
    		d: function destroy(detaching) {
    			destroy_component(notifications, detaching);
    			if (detaching) detach_dev(t0);
    			if (detaching) detach_dev(div);
    			destroy_component(blockpublicaccesswarning);
    			destroy_component(objectownershipenforcedwarning);
    			if (detaching) detach_dev(t2);
    			destroy_component(subnav, detaching);
    			if (detaching) detach_dev(t3);
    			destroy_component(subpages, detaching);
    			if (detaching) detach_dev(t4);
    			destroy_component(urlpreview, detaching);
    		}
    	};

    	dispatch_dev("SvelteRegisterBlock", {
    		block,
    		id: create_if_block_1$6.name,
    		type: "if",
    		source: "(52:1) {#if render}",
    		ctx
    	});

    	return block;
    }

    // (51:0) <Page {name} on:routeEvent>
    function create_default_slot$c(ctx) {
    	let if_block_anchor;
    	let current;
    	let if_block = /*render*/ ctx[2] && create_if_block_1$6(ctx);

    	const block = {
    		c: function create() {
    			if (if_block) if_block.c();
    			if_block_anchor = empty();
    		},
    		m: function mount(target, anchor) {
    			if (if_block) if_block.m(target, anchor);
    			insert_dev(target, if_block_anchor, anchor);
    			current = true;
    		},
    		p: function update(ctx, dirty) {
    			if (/*render*/ ctx[2]) {
    				if (if_block) {
    					if_block.p(ctx, dirty);

    					if (dirty & /*render*/ 4) {
    						transition_in(if_block, 1);
    					}
    				} else {
    					if_block = create_if_block_1$6(ctx);
    					if_block.c();
    					transition_in(if_block, 1);
    					if_block.m(if_block_anchor.parentNode, if_block_anchor);
    				}
    			} else if (if_block) {
    				group_outros();

    				transition_out(if_block, 1, 1, () => {
    					if_block = null;
    				});

    				check_outros();
    			}
    		},
    		i: function intro(local) {
    			if (current) return;
    			transition_in(if_block);
    			current = true;
    		},
    		o: function outro(local) {
    			transition_out(if_block);
    			current = false;
    		},
    		d: function destroy(detaching) {
    			if (if_block) if_block.d(detaching);
    			if (detaching) detach_dev(if_block_anchor);
    		}
    	};

    	dispatch_dev("SvelteRegisterBlock", {
    		block,
    		id: create_default_slot$c.name,
    		type: "slot",
    		source: "(51:0) <Page {name} on:routeEvent>",
    		ctx
    	});

    	return block;
    }

    // (64:0) {#if sidebar && render}
    function create_if_block$a(ctx) {
    	let switch_instance;
    	let switch_instance_anchor;
    	let current;
    	var switch_value = /*sidebar*/ ctx[1];

    	function switch_props(ctx) {
    		return { $$inline: true };
    	}

    	if (switch_value) {
    		switch_instance = new switch_value(switch_props());
    	}

    	const block = {
    		c: function create() {
    			if (switch_instance) create_component(switch_instance.$$.fragment);
    			switch_instance_anchor = empty();
    		},
    		m: function mount(target, anchor) {
    			if (switch_instance) {
    				mount_component(switch_instance, target, anchor);
    			}

    			insert_dev(target, switch_instance_anchor, anchor);
    			current = true;
    		},
    		p: function update(ctx, dirty) {
    			if (switch_value !== (switch_value = /*sidebar*/ ctx[1])) {
    				if (switch_instance) {
    					group_outros();
    					const old_component = switch_instance;

    					transition_out(old_component.$$.fragment, 1, 0, () => {
    						destroy_component(old_component, 1);
    					});

    					check_outros();
    				}

    				if (switch_value) {
    					switch_instance = new switch_value(switch_props());
    					create_component(switch_instance.$$.fragment);
    					transition_in(switch_instance.$$.fragment, 1);
    					mount_component(switch_instance, switch_instance_anchor.parentNode, switch_instance_anchor);
    				} else {
    					switch_instance = null;
    				}
    			}
    		},
    		i: function intro(local) {
    			if (current) return;
    			if (switch_instance) transition_in(switch_instance.$$.fragment, local);
    			current = true;
    		},
    		o: function outro(local) {
    			if (switch_instance) transition_out(switch_instance.$$.fragment, local);
    			current = false;
    		},
    		d: function destroy(detaching) {
    			if (detaching) detach_dev(switch_instance_anchor);
    			if (switch_instance) destroy_component(switch_instance, detaching);
    		}
    	};

    	dispatch_dev("SvelteRegisterBlock", {
    		block,
    		id: create_if_block$a.name,
    		type: "if",
    		source: "(64:0) {#if sidebar && render}",
    		ctx
    	});

    	return block;
    }

    function create_fragment$t(ctx) {
    	let page;
    	let t0;
    	let t1;
    	let footer;
    	let current;

    	page = new Page({
    			props: {
    				name: /*name*/ ctx[0],
    				$$slots: { default: [create_default_slot$c] },
    				$$scope: { ctx }
    			},
    			$$inline: true
    		});

    	page.$on("routeEvent", /*routeEvent_handler*/ ctx[6]);
    	let if_block = /*sidebar*/ ctx[1] && /*render*/ ctx[2] && create_if_block$a(ctx);
    	footer = new Footer({ $$inline: true });
    	footer.$on("routeEvent", /*routeEvent_handler_1*/ ctx[7]);

    	const block = {
    		c: function create() {
    			create_component(page.$$.fragment);
    			t0 = space();
    			if (if_block) if_block.c();
    			t1 = space();
    			create_component(footer.$$.fragment);
    		},
    		l: function claim(nodes) {
    			throw new Error("options.hydrate only works if the component was compiled with the `hydratable: true` option");
    		},
    		m: function mount(target, anchor) {
    			mount_component(page, target, anchor);
    			insert_dev(target, t0, anchor);
    			if (if_block) if_block.m(target, anchor);
    			insert_dev(target, t1, anchor);
    			mount_component(footer, target, anchor);
    			current = true;
    		},
    		p: function update(ctx, [dirty]) {
    			const page_changes = {};
    			if (dirty & /*name*/ 1) page_changes.name = /*name*/ ctx[0];

    			if (dirty & /*$$scope, name, render*/ 2053) {
    				page_changes.$$scope = { dirty, ctx };
    			}

    			page.$set(page_changes);

    			if (/*sidebar*/ ctx[1] && /*render*/ ctx[2]) {
    				if (if_block) {
    					if_block.p(ctx, dirty);

    					if (dirty & /*sidebar, render*/ 6) {
    						transition_in(if_block, 1);
    					}
    				} else {
    					if_block = create_if_block$a(ctx);
    					if_block.c();
    					transition_in(if_block, 1);
    					if_block.m(t1.parentNode, t1);
    				}
    			} else if (if_block) {
    				group_outros();

    				transition_out(if_block, 1, 1, () => {
    					if_block = null;
    				});

    				check_outros();
    			}
    		},
    		i: function intro(local) {
    			if (current) return;
    			transition_in(page.$$.fragment, local);
    			transition_in(if_block);
    			transition_in(footer.$$.fragment, local);
    			current = true;
    		},
    		o: function outro(local) {
    			transition_out(page.$$.fragment, local);
    			transition_out(if_block);
    			transition_out(footer.$$.fragment, local);
    			current = false;
    		},
    		d: function destroy(detaching) {
    			destroy_component(page, detaching);
    			if (detaching) detach_dev(t0);
    			if (if_block) if_block.d(detaching);
    			if (detaching) detach_dev(t1);
    			destroy_component(footer, detaching);
    		}
    	};

    	dispatch_dev("SvelteRegisterBlock", {
    		block,
    		id: create_fragment$t.name,
    		type: "component",
    		source: "",
    		ctx
    	});

    	return block;
    }

    function instance$t($$self, $$props, $$invalidate) {
    	let $is_plugin_setup;
    	let $strings;
    	validate_store(is_plugin_setup, 'is_plugin_setup');
    	component_subscribe($$self, is_plugin_setup, $$value => $$invalidate(8, $is_plugin_setup = $$value));
    	validate_store(strings, 'strings');
    	component_subscribe($$self, strings, $$value => $$invalidate(9, $strings = $$value));
    	let { $$slots: slots = {}, $$scope } = $$props;
    	validate_slots('MediaPage', slots, []);
    	let { name = "media" } = $$props;
    	let { params = {} } = $$props;
    	const _params = params; // Stops compiler warning for params;
    	let sidebar = null;
    	let render = false;

    	if (hasContext('sidebar')) {
    		sidebar = getContext('sidebar');
    	}

    	// Let all child components know if settings are currently locked.
    	setContext("settingsLocked", settingsLocked);

    	// We have a weird subnav here as both routes could be shown at same time.
    	// So they are grouped, and CSS decides which is shown when width stops both from being shown.
    	// The active route will determine the SubPage that is given the active class.
    	const routes = { '*': MediaSettings };

    	const items = [
    		{
    			route: "/",
    			title: () => $strings.storage_settings_title
    		},
    		{
    			route: "/media/delivery",
    			title: () => $strings.delivery_settings_title
    		}
    	];

    	onMount(() => {
    		if ($is_plugin_setup) {
    			$$invalidate(2, render = true);
    		}
    	});

    	const writable_props = ['name', 'params'];

    	Object.keys($$props).forEach(key => {
    		if (!~writable_props.indexOf(key) && key.slice(0, 2) !== '$$' && key !== 'slot') console.warn(`<MediaPage> was created with unknown prop '${key}'`);
    	});

    	function routeEvent_handler(event) {
    		bubble.call(this, $$self, event);
    	}

    	function routeEvent_handler_1(event) {
    		bubble.call(this, $$self, event);
    	}

    	$$self.$$set = $$props => {
    		if ('name' in $$props) $$invalidate(0, name = $$props.name);
    		if ('params' in $$props) $$invalidate(5, params = $$props.params);
    	};

    	$$self.$capture_state = () => ({
    		getContext,
    		hasContext,
    		onMount,
    		setContext,
    		is_plugin_setup,
    		settingsLocked,
    		strings,
    		Page,
    		Notifications,
    		BlockPublicAccessWarning,
    		ObjectOwnershipEnforcedWarning,
    		SubNav,
    		SubPages,
    		MediaSettings,
    		UrlPreview,
    		Footer,
    		name,
    		params,
    		_params,
    		sidebar,
    		render,
    		routes,
    		items,
    		$is_plugin_setup,
    		$strings
    	});

    	$$self.$inject_state = $$props => {
    		if ('name' in $$props) $$invalidate(0, name = $$props.name);
    		if ('params' in $$props) $$invalidate(5, params = $$props.params);
    		if ('sidebar' in $$props) $$invalidate(1, sidebar = $$props.sidebar);
    		if ('render' in $$props) $$invalidate(2, render = $$props.render);
    	};

    	if ($$props && "$$inject" in $$props) {
    		$$self.$inject_state($$props.$$inject);
    	}

    	return [
    		name,
    		sidebar,
    		render,
    		routes,
    		items,
    		params,
    		routeEvent_handler,
    		routeEvent_handler_1
    	];
    }

    class MediaPage extends SvelteComponentDev {
    	constructor(options) {
    		super(options);
    		init(this, options, instance$t, create_fragment$t, safe_not_equal, { name: 0, params: 5 });

    		dispatch_dev("SvelteRegisterComponent", {
    			component: this,
    			tagName: "MediaPage",
    			options,
    			id: create_fragment$t.name
    		});
    	}

    	get name() {
    		throw new Error("<MediaPage>: Props cannot be read directly from the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
    	}

    	set name(value) {
    		throw new Error("<MediaPage>: Props cannot be set directly on the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
    	}

    	get params() {
    		throw new Error("<MediaPage>: Props cannot be read directly from the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
    	}

    	set params(value) {
    		throw new Error("<MediaPage>: Props cannot be set directly on the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
    	}
    }

    /* src/amazon-s3-and-cloudfront/ui/components/StoragePage.svelte generated by Svelte v3.49.0 */
    const file$q = "src/amazon-s3-and-cloudfront/ui/components/StoragePage.svelte";

    // (47:0) <Page {name} subpage on:routeEvent>
    function create_default_slot$b(ctx) {
    	let notifications;
    	let t0;
    	let h2;
    	let t1_value = /*$strings*/ ctx[3].storage_title + "";
    	let t1;
    	let t2;
    	let subnav;
    	let t3;
    	let subpages;
    	let current;

    	notifications = new Notifications({
    			props: { tab: "media", tabParent: "media" },
    			$$inline: true
    		});

    	subnav = new SubNav({
    			props: {
    				name: /*name*/ ctx[0],
    				items: /*items*/ ctx[1],
    				progress: true
    			},
    			$$inline: true
    		});

    	subpages = new SubPages({
    			props: {
    				name: /*name*/ ctx[0],
    				prefix,
    				routes: /*routes*/ ctx[2]
    			},
    			$$inline: true
    		});

    	subpages.$on("routeEvent", /*routeEvent_handler_1*/ ctx[5]);

    	const block = {
    		c: function create() {
    			create_component(notifications.$$.fragment);
    			t0 = space();
    			h2 = element("h2");
    			t1 = text(t1_value);
    			t2 = space();
    			create_component(subnav.$$.fragment);
    			t3 = space();
    			create_component(subpages.$$.fragment);
    			attr_dev(h2, "class", "page-title");
    			add_location(h2, file$q, 48, 1, 1545);
    		},
    		m: function mount(target, anchor) {
    			mount_component(notifications, target, anchor);
    			insert_dev(target, t0, anchor);
    			insert_dev(target, h2, anchor);
    			append_dev(h2, t1);
    			insert_dev(target, t2, anchor);
    			mount_component(subnav, target, anchor);
    			insert_dev(target, t3, anchor);
    			mount_component(subpages, target, anchor);
    			current = true;
    		},
    		p: function update(ctx, dirty) {
    			if ((!current || dirty & /*$strings*/ 8) && t1_value !== (t1_value = /*$strings*/ ctx[3].storage_title + "")) set_data_dev(t1, t1_value);
    			const subnav_changes = {};
    			if (dirty & /*name*/ 1) subnav_changes.name = /*name*/ ctx[0];
    			if (dirty & /*items*/ 2) subnav_changes.items = /*items*/ ctx[1];
    			subnav.$set(subnav_changes);
    			const subpages_changes = {};
    			if (dirty & /*name*/ 1) subpages_changes.name = /*name*/ ctx[0];
    			if (dirty & /*routes*/ 4) subpages_changes.routes = /*routes*/ ctx[2];
    			subpages.$set(subpages_changes);
    		},
    		i: function intro(local) {
    			if (current) return;
    			transition_in(notifications.$$.fragment, local);
    			transition_in(subnav.$$.fragment, local);
    			transition_in(subpages.$$.fragment, local);
    			current = true;
    		},
    		o: function outro(local) {
    			transition_out(notifications.$$.fragment, local);
    			transition_out(subnav.$$.fragment, local);
    			transition_out(subpages.$$.fragment, local);
    			current = false;
    		},
    		d: function destroy(detaching) {
    			destroy_component(notifications, detaching);
    			if (detaching) detach_dev(t0);
    			if (detaching) detach_dev(h2);
    			if (detaching) detach_dev(t2);
    			destroy_component(subnav, detaching);
    			if (detaching) detach_dev(t3);
    			destroy_component(subpages, detaching);
    		}
    	};

    	dispatch_dev("SvelteRegisterBlock", {
    		block,
    		id: create_default_slot$b.name,
    		type: "slot",
    		source: "(47:0) <Page {name} subpage on:routeEvent>",
    		ctx
    	});

    	return block;
    }

    function create_fragment$s(ctx) {
    	let page;
    	let current;

    	page = new Page({
    			props: {
    				name: /*name*/ ctx[0],
    				subpage: true,
    				$$slots: { default: [create_default_slot$b] },
    				$$scope: { ctx }
    			},
    			$$inline: true
    		});

    	page.$on("routeEvent", /*routeEvent_handler*/ ctx[6]);

    	const block = {
    		c: function create() {
    			create_component(page.$$.fragment);
    		},
    		l: function claim(nodes) {
    			throw new Error("options.hydrate only works if the component was compiled with the `hydratable: true` option");
    		},
    		m: function mount(target, anchor) {
    			mount_component(page, target, anchor);
    			current = true;
    		},
    		p: function update(ctx, [dirty]) {
    			const page_changes = {};
    			if (dirty & /*name*/ 1) page_changes.name = /*name*/ ctx[0];

    			if (dirty & /*$$scope, name, routes, items, $strings*/ 2063) {
    				page_changes.$$scope = { dirty, ctx };
    			}

    			page.$set(page_changes);
    		},
    		i: function intro(local) {
    			if (current) return;
    			transition_in(page.$$.fragment, local);
    			current = true;
    		},
    		o: function outro(local) {
    			transition_out(page.$$.fragment, local);
    			current = false;
    		},
    		d: function destroy(detaching) {
    			destroy_component(page, detaching);
    		}
    	};

    	dispatch_dev("SvelteRegisterBlock", {
    		block,
    		id: create_fragment$s.name,
    		type: "component",
    		source: "",
    		ctx
    	});

    	return block;
    }

    const prefix = "/storage";

    function instance$s($$self, $$props, $$invalidate) {
    	let $location;
    	let $needs_access_keys;
    	let $current_settings;
    	let $strings;
    	validate_store(location$1, 'location');
    	component_subscribe($$self, location$1, $$value => $$invalidate(7, $location = $$value));
    	validate_store(needs_access_keys, 'needs_access_keys');
    	component_subscribe($$self, needs_access_keys, $$value => $$invalidate(8, $needs_access_keys = $$value));
    	validate_store(current_settings, 'current_settings');
    	component_subscribe($$self, current_settings, $$value => $$invalidate(9, $current_settings = $$value));
    	validate_store(strings, 'strings');
    	component_subscribe($$self, strings, $$value => $$invalidate(3, $strings = $$value));
    	let { $$slots: slots = {}, $$scope } = $$props;
    	validate_slots('StoragePage', slots, []);
    	let { name = "storage" } = $$props;
    	let { params = {} } = $$props;
    	const _params = params; // Stops compiler warning about unused params export;

    	// During initial setup some storage sub pages behave differently.
    	// Not having a bucket defined is akin to initial setup, but changing provider in sub page may also flip the switch.
    	if ($current_settings.bucket) {
    		setContext("initialSetup", false);
    	} else {
    		setContext("initialSetup", true);
    	}

    	// Let all child components know if settings are currently locked.
    	setContext("settingsLocked", settingsLocked);

    	let items = pages.withPrefix(prefix);
    	let routes = pages.routes(prefix);

    	afterUpdate(() => {
    		$$invalidate(1, items = pages.withPrefix(prefix));
    		$$invalidate(2, routes = pages.routes(prefix));

    		// Ensure only Storage Provider subpage can be visited if credentials not set.
    		if ($needs_access_keys && $location.startsWith("/storage/") && $location !== "/storage/provider") {
    			push("/storage/provider");
    		}
    	});

    	const writable_props = ['name', 'params'];

    	Object.keys($$props).forEach(key => {
    		if (!~writable_props.indexOf(key) && key.slice(0, 2) !== '$$' && key !== 'slot') console.warn(`<StoragePage> was created with unknown prop '${key}'`);
    	});

    	function routeEvent_handler_1(event) {
    		bubble.call(this, $$self, event);
    	}

    	function routeEvent_handler(event) {
    		bubble.call(this, $$self, event);
    	}

    	$$self.$$set = $$props => {
    		if ('name' in $$props) $$invalidate(0, name = $$props.name);
    		if ('params' in $$props) $$invalidate(4, params = $$props.params);
    	};

    	$$self.$capture_state = () => ({
    		afterUpdate,
    		setContext,
    		location: location$1,
    		push,
    		strings,
    		current_settings,
    		settingsLocked,
    		needs_access_keys,
    		Page,
    		Notifications,
    		SubNav,
    		SubPages,
    		pages,
    		name,
    		params,
    		_params,
    		prefix,
    		items,
    		routes,
    		$location,
    		$needs_access_keys,
    		$current_settings,
    		$strings
    	});

    	$$self.$inject_state = $$props => {
    		if ('name' in $$props) $$invalidate(0, name = $$props.name);
    		if ('params' in $$props) $$invalidate(4, params = $$props.params);
    		if ('items' in $$props) $$invalidate(1, items = $$props.items);
    		if ('routes' in $$props) $$invalidate(2, routes = $$props.routes);
    	};

    	if ($$props && "$$inject" in $$props) {
    		$$self.$inject_state($$props.$$inject);
    	}

    	return [
    		name,
    		items,
    		routes,
    		$strings,
    		params,
    		routeEvent_handler_1,
    		routeEvent_handler
    	];
    }

    class StoragePage extends SvelteComponentDev {
    	constructor(options) {
    		super(options);
    		init(this, options, instance$s, create_fragment$s, safe_not_equal, { name: 0, params: 4 });

    		dispatch_dev("SvelteRegisterComponent", {
    			component: this,
    			tagName: "StoragePage",
    			options,
    			id: create_fragment$s.name
    		});
    	}

    	get name() {
    		throw new Error("<StoragePage>: Props cannot be read directly from the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
    	}

    	set name(value) {
    		throw new Error("<StoragePage>: Props cannot be set directly on the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
    	}

    	get params() {
    		throw new Error("<StoragePage>: Props cannot be read directly from the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
    	}

    	set params(value) {
    		throw new Error("<StoragePage>: Props cannot be set directly on the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
    	}
    }

    /**
     * Determines whether a page should be refreshed due to changes to settings.
     *
     * @param {boolean} saving
     * @param {object} previousSettings
     * @param {object} currentSettings
     * @param {object} previousDefines
     * @param {object} currentDefines
     *
     * @returns {boolean}
     */
    function needsRefresh( saving, previousSettings, currentSettings, previousDefines, currentDefines ) {
    	if ( saving ) {
    		return false;
    	}

    	if ( objectsDiffer( [previousSettings, currentSettings] ) ) {
    		return true;
    	}

    	if ( objectsDiffer( [previousDefines, currentDefines] ) ) {
    		return true;
    	}
    }

    /* src/amazon-s3-and-cloudfront/ui/components/TabButton.svelte generated by Svelte v3.49.0 */
    const file$p = "src/amazon-s3-and-cloudfront/ui/components/TabButton.svelte";

    // (20:1) {#if icon}
    function create_if_block_2$3(ctx) {
    	let img;
    	let img_src_value;

    	const block = {
    		c: function create() {
    			img = element("img");
    			if (!src_url_equal(img.src, img_src_value = /*icon*/ ctx[2])) attr_dev(img, "src", img_src_value);
    			attr_dev(img, "type", "image/svg+xml");
    			attr_dev(img, "alt", /*iconDesc*/ ctx[3]);
    			add_location(img, file$p, 20, 2, 363);
    		},
    		m: function mount(target, anchor) {
    			insert_dev(target, img, anchor);
    		},
    		p: function update(ctx, dirty) {
    			if (dirty & /*icon*/ 4 && !src_url_equal(img.src, img_src_value = /*icon*/ ctx[2])) {
    				attr_dev(img, "src", img_src_value);
    			}

    			if (dirty & /*iconDesc*/ 8) {
    				attr_dev(img, "alt", /*iconDesc*/ ctx[3]);
    			}
    		},
    		d: function destroy(detaching) {
    			if (detaching) detach_dev(img);
    		}
    	};

    	dispatch_dev("SvelteRegisterBlock", {
    		block,
    		id: create_if_block_2$3.name,
    		type: "if",
    		source: "(20:1) {#if icon}",
    		ctx
    	});

    	return block;
    }

    // (27:1) {#if text}
    function create_if_block_1$5(ctx) {
    	let p;
    	let t;

    	const block = {
    		c: function create() {
    			p = element("p");
    			t = text(/*text*/ ctx[4]);
    			add_location(p, file$p, 27, 2, 449);
    		},
    		m: function mount(target, anchor) {
    			insert_dev(target, p, anchor);
    			append_dev(p, t);
    		},
    		p: function update(ctx, dirty) {
    			if (dirty & /*text*/ 16) set_data_dev(t, /*text*/ ctx[4]);
    		},
    		d: function destroy(detaching) {
    			if (detaching) detach_dev(p);
    		}
    	};

    	dispatch_dev("SvelteRegisterBlock", {
    		block,
    		id: create_if_block_1$5.name,
    		type: "if",
    		source: "(27:1) {#if text}",
    		ctx
    	});

    	return block;
    }

    // (30:1) {#if active}
    function create_if_block$9(ctx) {
    	let img;
    	let img_src_value;
    	let img_alt_value;

    	const block = {
    		c: function create() {
    			img = element("img");
    			attr_dev(img, "class", "checkmark");
    			if (!src_url_equal(img.src, img_src_value = /*$urls*/ ctx[6].assets + 'img/icon/licence-checked.svg')) attr_dev(img, "src", img_src_value);
    			attr_dev(img, "type", "image/svg+xml");
    			attr_dev(img, "alt", img_alt_value = /*$strings*/ ctx[7].selected_desc);
    			add_location(img, file$p, 30, 2, 486);
    		},
    		m: function mount(target, anchor) {
    			insert_dev(target, img, anchor);
    		},
    		p: function update(ctx, dirty) {
    			if (dirty & /*$urls*/ 64 && !src_url_equal(img.src, img_src_value = /*$urls*/ ctx[6].assets + 'img/icon/licence-checked.svg')) {
    				attr_dev(img, "src", img_src_value);
    			}

    			if (dirty & /*$strings*/ 128 && img_alt_value !== (img_alt_value = /*$strings*/ ctx[7].selected_desc)) {
    				attr_dev(img, "alt", img_alt_value);
    			}
    		},
    		d: function destroy(detaching) {
    			if (detaching) detach_dev(img);
    		}
    	};

    	dispatch_dev("SvelteRegisterBlock", {
    		block,
    		id: create_if_block$9.name,
    		type: "if",
    		source: "(30:1) {#if active}",
    		ctx
    	});

    	return block;
    }

    function create_fragment$r(ctx) {
    	let a;
    	let t0;
    	let t1;
    	let mounted;
    	let dispose;
    	let if_block0 = /*icon*/ ctx[2] && create_if_block_2$3(ctx);
    	let if_block1 = /*text*/ ctx[4] && create_if_block_1$5(ctx);
    	let if_block2 = /*active*/ ctx[0] && create_if_block$9(ctx);

    	const block = {
    		c: function create() {
    			a = element("a");
    			if (if_block0) if_block0.c();
    			t0 = space();
    			if (if_block1) if_block1.c();
    			t1 = space();
    			if (if_block2) if_block2.c();
    			attr_dev(a, "href", /*url*/ ctx[5]);
    			attr_dev(a, "class", "button-tab");
    			attr_dev(a, "disabled", /*disabled*/ ctx[1]);
    			toggle_class(a, "active", /*active*/ ctx[0]);
    			toggle_class(a, "btn-disabled", /*disabled*/ ctx[1]);
    			add_location(a, file$p, 11, 0, 230);
    		},
    		l: function claim(nodes) {
    			throw new Error("options.hydrate only works if the component was compiled with the `hydratable: true` option");
    		},
    		m: function mount(target, anchor) {
    			insert_dev(target, a, anchor);
    			if (if_block0) if_block0.m(a, null);
    			append_dev(a, t0);
    			if (if_block1) if_block1.m(a, null);
    			append_dev(a, t1);
    			if (if_block2) if_block2.m(a, null);

    			if (!mounted) {
    				dispose = listen_dev(a, "click", prevent_default(/*click_handler*/ ctx[8]), false, true, false);
    				mounted = true;
    			}
    		},
    		p: function update(ctx, [dirty]) {
    			if (/*icon*/ ctx[2]) {
    				if (if_block0) {
    					if_block0.p(ctx, dirty);
    				} else {
    					if_block0 = create_if_block_2$3(ctx);
    					if_block0.c();
    					if_block0.m(a, t0);
    				}
    			} else if (if_block0) {
    				if_block0.d(1);
    				if_block0 = null;
    			}

    			if (/*text*/ ctx[4]) {
    				if (if_block1) {
    					if_block1.p(ctx, dirty);
    				} else {
    					if_block1 = create_if_block_1$5(ctx);
    					if_block1.c();
    					if_block1.m(a, t1);
    				}
    			} else if (if_block1) {
    				if_block1.d(1);
    				if_block1 = null;
    			}

    			if (/*active*/ ctx[0]) {
    				if (if_block2) {
    					if_block2.p(ctx, dirty);
    				} else {
    					if_block2 = create_if_block$9(ctx);
    					if_block2.c();
    					if_block2.m(a, null);
    				}
    			} else if (if_block2) {
    				if_block2.d(1);
    				if_block2 = null;
    			}

    			if (dirty & /*url*/ 32) {
    				attr_dev(a, "href", /*url*/ ctx[5]);
    			}

    			if (dirty & /*disabled*/ 2) {
    				attr_dev(a, "disabled", /*disabled*/ ctx[1]);
    			}

    			if (dirty & /*active*/ 1) {
    				toggle_class(a, "active", /*active*/ ctx[0]);
    			}

    			if (dirty & /*disabled*/ 2) {
    				toggle_class(a, "btn-disabled", /*disabled*/ ctx[1]);
    			}
    		},
    		i: noop,
    		o: noop,
    		d: function destroy(detaching) {
    			if (detaching) detach_dev(a);
    			if (if_block0) if_block0.d();
    			if (if_block1) if_block1.d();
    			if (if_block2) if_block2.d();
    			mounted = false;
    			dispose();
    		}
    	};

    	dispatch_dev("SvelteRegisterBlock", {
    		block,
    		id: create_fragment$r.name,
    		type: "component",
    		source: "",
    		ctx
    	});

    	return block;
    }

    function instance$r($$self, $$props, $$invalidate) {
    	let $urls;
    	let $strings;
    	validate_store(urls, 'urls');
    	component_subscribe($$self, urls, $$value => $$invalidate(6, $urls = $$value));
    	validate_store(strings, 'strings');
    	component_subscribe($$self, strings, $$value => $$invalidate(7, $strings = $$value));
    	let { $$slots: slots = {}, $$scope } = $$props;
    	validate_slots('TabButton', slots, []);
    	let { active = false } = $$props;
    	let { disabled = false } = $$props;
    	let { icon = "" } = $$props;
    	let { iconDesc = "" } = $$props;
    	let { text = "" } = $$props;
    	let { url = $urls.settings } = $$props;
    	const writable_props = ['active', 'disabled', 'icon', 'iconDesc', 'text', 'url'];

    	Object.keys($$props).forEach(key => {
    		if (!~writable_props.indexOf(key) && key.slice(0, 2) !== '$$' && key !== 'slot') console.warn(`<TabButton> was created with unknown prop '${key}'`);
    	});

    	function click_handler(event) {
    		bubble.call(this, $$self, event);
    	}

    	$$self.$$set = $$props => {
    		if ('active' in $$props) $$invalidate(0, active = $$props.active);
    		if ('disabled' in $$props) $$invalidate(1, disabled = $$props.disabled);
    		if ('icon' in $$props) $$invalidate(2, icon = $$props.icon);
    		if ('iconDesc' in $$props) $$invalidate(3, iconDesc = $$props.iconDesc);
    		if ('text' in $$props) $$invalidate(4, text = $$props.text);
    		if ('url' in $$props) $$invalidate(5, url = $$props.url);
    	};

    	$$self.$capture_state = () => ({
    		strings,
    		urls,
    		active,
    		disabled,
    		icon,
    		iconDesc,
    		text,
    		url,
    		$urls,
    		$strings
    	});

    	$$self.$inject_state = $$props => {
    		if ('active' in $$props) $$invalidate(0, active = $$props.active);
    		if ('disabled' in $$props) $$invalidate(1, disabled = $$props.disabled);
    		if ('icon' in $$props) $$invalidate(2, icon = $$props.icon);
    		if ('iconDesc' in $$props) $$invalidate(3, iconDesc = $$props.iconDesc);
    		if ('text' in $$props) $$invalidate(4, text = $$props.text);
    		if ('url' in $$props) $$invalidate(5, url = $$props.url);
    	};

    	if ($$props && "$$inject" in $$props) {
    		$$self.$inject_state($$props.$$inject);
    	}

    	return [active, disabled, icon, iconDesc, text, url, $urls, $strings, click_handler];
    }

    class TabButton extends SvelteComponentDev {
    	constructor(options) {
    		super(options);

    		init(this, options, instance$r, create_fragment$r, safe_not_equal, {
    			active: 0,
    			disabled: 1,
    			icon: 2,
    			iconDesc: 3,
    			text: 4,
    			url: 5
    		});

    		dispatch_dev("SvelteRegisterComponent", {
    			component: this,
    			tagName: "TabButton",
    			options,
    			id: create_fragment$r.name
    		});
    	}

    	get active() {
    		throw new Error("<TabButton>: Props cannot be read directly from the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
    	}

    	set active(value) {
    		throw new Error("<TabButton>: Props cannot be set directly on the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
    	}

    	get disabled() {
    		throw new Error("<TabButton>: Props cannot be read directly from the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
    	}

    	set disabled(value) {
    		throw new Error("<TabButton>: Props cannot be set directly on the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
    	}

    	get icon() {
    		throw new Error("<TabButton>: Props cannot be read directly from the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
    	}

    	set icon(value) {
    		throw new Error("<TabButton>: Props cannot be set directly on the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
    	}

    	get iconDesc() {
    		throw new Error("<TabButton>: Props cannot be read directly from the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
    	}

    	set iconDesc(value) {
    		throw new Error("<TabButton>: Props cannot be set directly on the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
    	}

    	get text() {
    		throw new Error("<TabButton>: Props cannot be read directly from the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
    	}

    	set text(value) {
    		throw new Error("<TabButton>: Props cannot be set directly on the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
    	}

    	get url() {
    		throw new Error("<TabButton>: Props cannot be read directly from the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
    	}

    	set url(value) {
    		throw new Error("<TabButton>: Props cannot be set directly on the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
    	}
    }

    /* src/amazon-s3-and-cloudfront/ui/components/RadioButton.svelte generated by Svelte v3.49.0 */

    const file$o = "src/amazon-s3-and-cloudfront/ui/components/RadioButton.svelte";

    // (16:0) {#if selected === value && desc}
    function create_if_block$8(ctx) {
    	let p;

    	const block = {
    		c: function create() {
    			p = element("p");
    			attr_dev(p, "class", "radio-desc");
    			add_location(p, file$o, 16, 1, 371);
    		},
    		m: function mount(target, anchor) {
    			insert_dev(target, p, anchor);
    			p.innerHTML = /*desc*/ ctx[5];
    		},
    		p: function update(ctx, dirty) {
    			if (dirty & /*desc*/ 32) p.innerHTML = /*desc*/ ctx[5];		},
    		d: function destroy(detaching) {
    			if (detaching) detach_dev(p);
    		}
    	};

    	dispatch_dev("SvelteRegisterBlock", {
    		block,
    		id: create_if_block$8.name,
    		type: "if",
    		source: "(16:0) {#if selected === value && desc}",
    		ctx
    	});

    	return block;
    }

    function create_fragment$q(ctx) {
    	let div;
    	let label;
    	let input;
    	let t0;
    	let t1;
    	let if_block_anchor;
    	let current;
    	let mounted;
    	let dispose;
    	const default_slot_template = /*#slots*/ ctx[7].default;
    	const default_slot = create_slot(default_slot_template, ctx, /*$$scope*/ ctx[6], null);
    	let if_block = /*selected*/ ctx[0] === /*value*/ ctx[4] && /*desc*/ ctx[5] && create_if_block$8(ctx);

    	const block = {
    		c: function create() {
    			div = element("div");
    			label = element("label");
    			input = element("input");
    			t0 = space();
    			if (default_slot) default_slot.c();
    			t1 = space();
    			if (if_block) if_block.c();
    			if_block_anchor = empty();
    			attr_dev(input, "type", "radio");
    			attr_dev(input, "name", /*name*/ ctx[3]);
    			input.__value = /*value*/ ctx[4];
    			input.value = input.__value;
    			input.disabled = /*disabled*/ ctx[2];
    			/*$$binding_groups*/ ctx[9][0].push(input);
    			add_location(input, file$o, 11, 2, 241);
    			add_location(label, file$o, 10, 1, 231);
    			attr_dev(div, "class", "radio-btn");
    			toggle_class(div, "list", /*list*/ ctx[1]);
    			toggle_class(div, "disabled", /*disabled*/ ctx[2]);
    			add_location(div, file$o, 9, 0, 180);
    		},
    		l: function claim(nodes) {
    			throw new Error("options.hydrate only works if the component was compiled with the `hydratable: true` option");
    		},
    		m: function mount(target, anchor) {
    			insert_dev(target, div, anchor);
    			append_dev(div, label);
    			append_dev(label, input);
    			input.checked = input.__value === /*selected*/ ctx[0];
    			append_dev(label, t0);

    			if (default_slot) {
    				default_slot.m(label, null);
    			}

    			insert_dev(target, t1, anchor);
    			if (if_block) if_block.m(target, anchor);
    			insert_dev(target, if_block_anchor, anchor);
    			current = true;

    			if (!mounted) {
    				dispose = listen_dev(input, "change", /*input_change_handler*/ ctx[8]);
    				mounted = true;
    			}
    		},
    		p: function update(ctx, [dirty]) {
    			if (!current || dirty & /*name*/ 8) {
    				attr_dev(input, "name", /*name*/ ctx[3]);
    			}

    			if (!current || dirty & /*value*/ 16) {
    				prop_dev(input, "__value", /*value*/ ctx[4]);
    				input.value = input.__value;
    			}

    			if (!current || dirty & /*disabled*/ 4) {
    				prop_dev(input, "disabled", /*disabled*/ ctx[2]);
    			}

    			if (dirty & /*selected*/ 1) {
    				input.checked = input.__value === /*selected*/ ctx[0];
    			}

    			if (default_slot) {
    				if (default_slot.p && (!current || dirty & /*$$scope*/ 64)) {
    					update_slot_base(
    						default_slot,
    						default_slot_template,
    						ctx,
    						/*$$scope*/ ctx[6],
    						!current
    						? get_all_dirty_from_scope(/*$$scope*/ ctx[6])
    						: get_slot_changes(default_slot_template, /*$$scope*/ ctx[6], dirty, null),
    						null
    					);
    				}
    			}

    			if (dirty & /*list*/ 2) {
    				toggle_class(div, "list", /*list*/ ctx[1]);
    			}

    			if (dirty & /*disabled*/ 4) {
    				toggle_class(div, "disabled", /*disabled*/ ctx[2]);
    			}

    			if (/*selected*/ ctx[0] === /*value*/ ctx[4] && /*desc*/ ctx[5]) {
    				if (if_block) {
    					if_block.p(ctx, dirty);
    				} else {
    					if_block = create_if_block$8(ctx);
    					if_block.c();
    					if_block.m(if_block_anchor.parentNode, if_block_anchor);
    				}
    			} else if (if_block) {
    				if_block.d(1);
    				if_block = null;
    			}
    		},
    		i: function intro(local) {
    			if (current) return;
    			transition_in(default_slot, local);
    			current = true;
    		},
    		o: function outro(local) {
    			transition_out(default_slot, local);
    			current = false;
    		},
    		d: function destroy(detaching) {
    			if (detaching) detach_dev(div);
    			/*$$binding_groups*/ ctx[9][0].splice(/*$$binding_groups*/ ctx[9][0].indexOf(input), 1);
    			if (default_slot) default_slot.d(detaching);
    			if (detaching) detach_dev(t1);
    			if (if_block) if_block.d(detaching);
    			if (detaching) detach_dev(if_block_anchor);
    			mounted = false;
    			dispose();
    		}
    	};

    	dispatch_dev("SvelteRegisterBlock", {
    		block,
    		id: create_fragment$q.name,
    		type: "component",
    		source: "",
    		ctx
    	});

    	return block;
    }

    function instance$q($$self, $$props, $$invalidate) {
    	let { $$slots: slots = {}, $$scope } = $$props;
    	validate_slots('RadioButton', slots, ['default']);
    	let { list = false } = $$props;
    	let { disabled = false } = $$props;
    	let { name = "options" } = $$props;
    	let { value = "" } = $$props;
    	let { selected = "" } = $$props;
    	let { desc = "" } = $$props;
    	const writable_props = ['list', 'disabled', 'name', 'value', 'selected', 'desc'];

    	Object.keys($$props).forEach(key => {
    		if (!~writable_props.indexOf(key) && key.slice(0, 2) !== '$$' && key !== 'slot') console.warn(`<RadioButton> was created with unknown prop '${key}'`);
    	});

    	const $$binding_groups = [[]];

    	function input_change_handler() {
    		selected = this.__value;
    		$$invalidate(0, selected);
    	}

    	$$self.$$set = $$props => {
    		if ('list' in $$props) $$invalidate(1, list = $$props.list);
    		if ('disabled' in $$props) $$invalidate(2, disabled = $$props.disabled);
    		if ('name' in $$props) $$invalidate(3, name = $$props.name);
    		if ('value' in $$props) $$invalidate(4, value = $$props.value);
    		if ('selected' in $$props) $$invalidate(0, selected = $$props.selected);
    		if ('desc' in $$props) $$invalidate(5, desc = $$props.desc);
    		if ('$$scope' in $$props) $$invalidate(6, $$scope = $$props.$$scope);
    	};

    	$$self.$capture_state = () => ({
    		list,
    		disabled,
    		name,
    		value,
    		selected,
    		desc
    	});

    	$$self.$inject_state = $$props => {
    		if ('list' in $$props) $$invalidate(1, list = $$props.list);
    		if ('disabled' in $$props) $$invalidate(2, disabled = $$props.disabled);
    		if ('name' in $$props) $$invalidate(3, name = $$props.name);
    		if ('value' in $$props) $$invalidate(4, value = $$props.value);
    		if ('selected' in $$props) $$invalidate(0, selected = $$props.selected);
    		if ('desc' in $$props) $$invalidate(5, desc = $$props.desc);
    	};

    	if ($$props && "$$inject" in $$props) {
    		$$self.$inject_state($$props.$$inject);
    	}

    	return [
    		selected,
    		list,
    		disabled,
    		name,
    		value,
    		desc,
    		$$scope,
    		slots,
    		input_change_handler,
    		$$binding_groups
    	];
    }

    class RadioButton extends SvelteComponentDev {
    	constructor(options) {
    		super(options);

    		init(this, options, instance$q, create_fragment$q, safe_not_equal, {
    			list: 1,
    			disabled: 2,
    			name: 3,
    			value: 4,
    			selected: 0,
    			desc: 5
    		});

    		dispatch_dev("SvelteRegisterComponent", {
    			component: this,
    			tagName: "RadioButton",
    			options,
    			id: create_fragment$q.name
    		});
    	}

    	get list() {
    		throw new Error("<RadioButton>: Props cannot be read directly from the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
    	}

    	set list(value) {
    		throw new Error("<RadioButton>: Props cannot be set directly on the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
    	}

    	get disabled() {
    		throw new Error("<RadioButton>: Props cannot be read directly from the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
    	}

    	set disabled(value) {
    		throw new Error("<RadioButton>: Props cannot be set directly on the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
    	}

    	get name() {
    		throw new Error("<RadioButton>: Props cannot be read directly from the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
    	}

    	set name(value) {
    		throw new Error("<RadioButton>: Props cannot be set directly on the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
    	}

    	get value() {
    		throw new Error("<RadioButton>: Props cannot be read directly from the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
    	}

    	set value(value) {
    		throw new Error("<RadioButton>: Props cannot be set directly on the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
    	}

    	get selected() {
    		throw new Error("<RadioButton>: Props cannot be read directly from the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
    	}

    	set selected(value) {
    		throw new Error("<RadioButton>: Props cannot be set directly on the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
    	}

    	get desc() {
    		throw new Error("<RadioButton>: Props cannot be read directly from the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
    	}

    	set desc(value) {
    		throw new Error("<RadioButton>: Props cannot be set directly on the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
    	}
    }

    /* src/amazon-s3-and-cloudfront/ui/components/AccessKeysDefine.svelte generated by Svelte v3.49.0 */

    const file$n = "src/amazon-s3-and-cloudfront/ui/components/AccessKeysDefine.svelte";

    function create_fragment$p(ctx) {
    	let p;
    	let raw_value = /*provider*/ ctx[0].define_access_keys_desc + "";
    	let t0;
    	let pre;
    	let t1_value = /*provider*/ ctx[0].define_access_keys_example + "";
    	let t1;

    	const block = {
    		c: function create() {
    			p = element("p");
    			t0 = space();
    			pre = element("pre");
    			t1 = text(t1_value);
    			add_location(p, file$n, 4, 0, 42);
    			add_location(pre, file$n, 6, 0, 91);
    		},
    		l: function claim(nodes) {
    			throw new Error("options.hydrate only works if the component was compiled with the `hydratable: true` option");
    		},
    		m: function mount(target, anchor) {
    			insert_dev(target, p, anchor);
    			p.innerHTML = raw_value;
    			insert_dev(target, t0, anchor);
    			insert_dev(target, pre, anchor);
    			append_dev(pre, t1);
    		},
    		p: function update(ctx, [dirty]) {
    			if (dirty & /*provider*/ 1 && raw_value !== (raw_value = /*provider*/ ctx[0].define_access_keys_desc + "")) p.innerHTML = raw_value;			if (dirty & /*provider*/ 1 && t1_value !== (t1_value = /*provider*/ ctx[0].define_access_keys_example + "")) set_data_dev(t1, t1_value);
    		},
    		i: noop,
    		o: noop,
    		d: function destroy(detaching) {
    			if (detaching) detach_dev(p);
    			if (detaching) detach_dev(t0);
    			if (detaching) detach_dev(pre);
    		}
    	};

    	dispatch_dev("SvelteRegisterBlock", {
    		block,
    		id: create_fragment$p.name,
    		type: "component",
    		source: "",
    		ctx
    	});

    	return block;
    }

    function instance$p($$self, $$props, $$invalidate) {
    	let { $$slots: slots = {}, $$scope } = $$props;
    	validate_slots('AccessKeysDefine', slots, []);
    	let { provider } = $$props;
    	const writable_props = ['provider'];

    	Object.keys($$props).forEach(key => {
    		if (!~writable_props.indexOf(key) && key.slice(0, 2) !== '$$' && key !== 'slot') console.warn(`<AccessKeysDefine> was created with unknown prop '${key}'`);
    	});

    	$$self.$$set = $$props => {
    		if ('provider' in $$props) $$invalidate(0, provider = $$props.provider);
    	};

    	$$self.$capture_state = () => ({ provider });

    	$$self.$inject_state = $$props => {
    		if ('provider' in $$props) $$invalidate(0, provider = $$props.provider);
    	};

    	if ($$props && "$$inject" in $$props) {
    		$$self.$inject_state($$props.$$inject);
    	}

    	return [provider];
    }

    class AccessKeysDefine extends SvelteComponentDev {
    	constructor(options) {
    		super(options);
    		init(this, options, instance$p, create_fragment$p, safe_not_equal, { provider: 0 });

    		dispatch_dev("SvelteRegisterComponent", {
    			component: this,
    			tagName: "AccessKeysDefine",
    			options,
    			id: create_fragment$p.name
    		});

    		const { ctx } = this.$$;
    		const props = options.props || {};

    		if (/*provider*/ ctx[0] === undefined && !('provider' in props)) {
    			console.warn("<AccessKeysDefine> was created without expected prop 'provider'");
    		}
    	}

    	get provider() {
    		throw new Error("<AccessKeysDefine>: Props cannot be read directly from the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
    	}

    	set provider(value) {
    		throw new Error("<AccessKeysDefine>: Props cannot be set directly on the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
    	}
    }

    /* src/amazon-s3-and-cloudfront/ui/components/BackNextButtonsRow.svelte generated by Svelte v3.49.0 */
    const file$m = "src/amazon-s3-and-cloudfront/ui/components/BackNextButtonsRow.svelte";

    // (27:1) {#if backVisible}
    function create_if_block_1$4(ctx) {
    	let button;
    	let current;

    	button = new Button({
    			props: {
    				large: true,
    				disabled: /*backDisabled*/ ctx[1],
    				title: /*backTitle*/ ctx[2],
    				$$slots: { default: [create_default_slot_2$4] },
    				$$scope: { ctx }
    			},
    			$$inline: true
    		});

    	button.$on("click", /*click_handler*/ ctx[12]);

    	const block = {
    		c: function create() {
    			create_component(button.$$.fragment);
    		},
    		m: function mount(target, anchor) {
    			mount_component(button, target, anchor);
    			current = true;
    		},
    		p: function update(ctx, dirty) {
    			const button_changes = {};
    			if (dirty & /*backDisabled*/ 2) button_changes.disabled = /*backDisabled*/ ctx[1];
    			if (dirty & /*backTitle*/ 4) button_changes.title = /*backTitle*/ ctx[2];

    			if (dirty & /*$$scope, backText*/ 65537) {
    				button_changes.$$scope = { dirty, ctx };
    			}

    			button.$set(button_changes);
    		},
    		i: function intro(local) {
    			if (current) return;
    			transition_in(button.$$.fragment, local);
    			current = true;
    		},
    		o: function outro(local) {
    			transition_out(button.$$.fragment, local);
    			current = false;
    		},
    		d: function destroy(detaching) {
    			destroy_component(button, detaching);
    		}
    	};

    	dispatch_dev("SvelteRegisterBlock", {
    		block,
    		id: create_if_block_1$4.name,
    		type: "if",
    		source: "(27:1) {#if backVisible}",
    		ctx
    	});

    	return block;
    }

    // (28:2) <Button    large    on:click="{() => dispatch('back')}"    disabled={backDisabled}    title={backTitle}   >
    function create_default_slot_2$4(ctx) {
    	let t;

    	const block = {
    		c: function create() {
    			t = text(/*backText*/ ctx[0]);
    		},
    		m: function mount(target, anchor) {
    			insert_dev(target, t, anchor);
    		},
    		p: function update(ctx, dirty) {
    			if (dirty & /*backText*/ 1) set_data_dev(t, /*backText*/ ctx[0]);
    		},
    		d: function destroy(detaching) {
    			if (detaching) detach_dev(t);
    		}
    	};

    	dispatch_dev("SvelteRegisterBlock", {
    		block,
    		id: create_default_slot_2$4.name,
    		type: "slot",
    		source: "(28:2) <Button    large    on:click=\\\"{() => dispatch('back')}\\\"    disabled={backDisabled}    title={backTitle}   >",
    		ctx
    	});

    	return block;
    }

    // (37:1) {#if skipVisible}
    function create_if_block$7(ctx) {
    	let button;
    	let current;

    	button = new Button({
    			props: {
    				large: true,
    				outline: true,
    				disabled: /*skipDisabled*/ ctx[5],
    				title: /*skipTitle*/ ctx[6],
    				$$slots: { default: [create_default_slot_1$4] },
    				$$scope: { ctx }
    			},
    			$$inline: true
    		});

    	button.$on("click", /*click_handler_1*/ ctx[13]);

    	const block = {
    		c: function create() {
    			create_component(button.$$.fragment);
    		},
    		m: function mount(target, anchor) {
    			mount_component(button, target, anchor);
    			current = true;
    		},
    		p: function update(ctx, dirty) {
    			const button_changes = {};
    			if (dirty & /*skipDisabled*/ 32) button_changes.disabled = /*skipDisabled*/ ctx[5];
    			if (dirty & /*skipTitle*/ 64) button_changes.title = /*skipTitle*/ ctx[6];

    			if (dirty & /*$$scope, skipText*/ 65552) {
    				button_changes.$$scope = { dirty, ctx };
    			}

    			button.$set(button_changes);
    		},
    		i: function intro(local) {
    			if (current) return;
    			transition_in(button.$$.fragment, local);
    			current = true;
    		},
    		o: function outro(local) {
    			transition_out(button.$$.fragment, local);
    			current = false;
    		},
    		d: function destroy(detaching) {
    			destroy_component(button, detaching);
    		}
    	};

    	dispatch_dev("SvelteRegisterBlock", {
    		block,
    		id: create_if_block$7.name,
    		type: "if",
    		source: "(37:1) {#if skipVisible}",
    		ctx
    	});

    	return block;
    }

    // (38:2) <Button    large    outline    on:click="{() => dispatch('skip')}"    disabled={skipDisabled}    title={skipTitle}   >
    function create_default_slot_1$4(ctx) {
    	let t;

    	const block = {
    		c: function create() {
    			t = text(/*skipText*/ ctx[4]);
    		},
    		m: function mount(target, anchor) {
    			insert_dev(target, t, anchor);
    		},
    		p: function update(ctx, dirty) {
    			if (dirty & /*skipText*/ 16) set_data_dev(t, /*skipText*/ ctx[4]);
    		},
    		d: function destroy(detaching) {
    			if (detaching) detach_dev(t);
    		}
    	};

    	dispatch_dev("SvelteRegisterBlock", {
    		block,
    		id: create_default_slot_1$4.name,
    		type: "slot",
    		source: "(38:2) <Button    large    outline    on:click=\\\"{() => dispatch('skip')}\\\"    disabled={skipDisabled}    title={skipTitle}   >",
    		ctx
    	});

    	return block;
    }

    // (48:1) <Button   large   primary   on:click="{() => dispatch('next')}"   disabled={nextDisabled}   title={nextTitle}  >
    function create_default_slot$a(ctx) {
    	let t;

    	const block = {
    		c: function create() {
    			t = text(/*nextText*/ ctx[8]);
    		},
    		m: function mount(target, anchor) {
    			insert_dev(target, t, anchor);
    		},
    		p: function update(ctx, dirty) {
    			if (dirty & /*nextText*/ 256) set_data_dev(t, /*nextText*/ ctx[8]);
    		},
    		d: function destroy(detaching) {
    			if (detaching) detach_dev(t);
    		}
    	};

    	dispatch_dev("SvelteRegisterBlock", {
    		block,
    		id: create_default_slot$a.name,
    		type: "slot",
    		source: "(48:1) <Button   large   primary   on:click=\\\"{() => dispatch('next')}\\\"   disabled={nextDisabled}   title={nextTitle}  >",
    		ctx
    	});

    	return block;
    }

    function create_fragment$o(ctx) {
    	let div;
    	let t0;
    	let t1;
    	let button;
    	let current;
    	let if_block0 = /*backVisible*/ ctx[3] && create_if_block_1$4(ctx);
    	let if_block1 = /*skipVisible*/ ctx[7] && create_if_block$7(ctx);

    	button = new Button({
    			props: {
    				large: true,
    				primary: true,
    				disabled: /*nextDisabled*/ ctx[9],
    				title: /*nextTitle*/ ctx[10],
    				$$slots: { default: [create_default_slot$a] },
    				$$scope: { ctx }
    			},
    			$$inline: true
    		});

    	button.$on("click", /*click_handler_2*/ ctx[14]);

    	const block = {
    		c: function create() {
    			div = element("div");
    			if (if_block0) if_block0.c();
    			t0 = space();
    			if (if_block1) if_block1.c();
    			t1 = space();
    			create_component(button.$$.fragment);
    			attr_dev(div, "class", "btn-row");
    			add_location(div, file$m, 25, 0, 702);
    		},
    		l: function claim(nodes) {
    			throw new Error("options.hydrate only works if the component was compiled with the `hydratable: true` option");
    		},
    		m: function mount(target, anchor) {
    			insert_dev(target, div, anchor);
    			if (if_block0) if_block0.m(div, null);
    			append_dev(div, t0);
    			if (if_block1) if_block1.m(div, null);
    			append_dev(div, t1);
    			mount_component(button, div, null);
    			current = true;
    		},
    		p: function update(ctx, [dirty]) {
    			if (/*backVisible*/ ctx[3]) {
    				if (if_block0) {
    					if_block0.p(ctx, dirty);

    					if (dirty & /*backVisible*/ 8) {
    						transition_in(if_block0, 1);
    					}
    				} else {
    					if_block0 = create_if_block_1$4(ctx);
    					if_block0.c();
    					transition_in(if_block0, 1);
    					if_block0.m(div, t0);
    				}
    			} else if (if_block0) {
    				group_outros();

    				transition_out(if_block0, 1, 1, () => {
    					if_block0 = null;
    				});

    				check_outros();
    			}

    			if (/*skipVisible*/ ctx[7]) {
    				if (if_block1) {
    					if_block1.p(ctx, dirty);

    					if (dirty & /*skipVisible*/ 128) {
    						transition_in(if_block1, 1);
    					}
    				} else {
    					if_block1 = create_if_block$7(ctx);
    					if_block1.c();
    					transition_in(if_block1, 1);
    					if_block1.m(div, t1);
    				}
    			} else if (if_block1) {
    				group_outros();

    				transition_out(if_block1, 1, 1, () => {
    					if_block1 = null;
    				});

    				check_outros();
    			}

    			const button_changes = {};
    			if (dirty & /*nextDisabled*/ 512) button_changes.disabled = /*nextDisabled*/ ctx[9];
    			if (dirty & /*nextTitle*/ 1024) button_changes.title = /*nextTitle*/ ctx[10];

    			if (dirty & /*$$scope, nextText*/ 65792) {
    				button_changes.$$scope = { dirty, ctx };
    			}

    			button.$set(button_changes);
    		},
    		i: function intro(local) {
    			if (current) return;
    			transition_in(if_block0);
    			transition_in(if_block1);
    			transition_in(button.$$.fragment, local);
    			current = true;
    		},
    		o: function outro(local) {
    			transition_out(if_block0);
    			transition_out(if_block1);
    			transition_out(button.$$.fragment, local);
    			current = false;
    		},
    		d: function destroy(detaching) {
    			if (detaching) detach_dev(div);
    			if (if_block0) if_block0.d();
    			if (if_block1) if_block1.d();
    			destroy_component(button);
    		}
    	};

    	dispatch_dev("SvelteRegisterBlock", {
    		block,
    		id: create_fragment$o.name,
    		type: "component",
    		source: "",
    		ctx
    	});

    	return block;
    }

    function instance$o($$self, $$props, $$invalidate) {
    	let $strings;
    	validate_store(strings, 'strings');
    	component_subscribe($$self, strings, $$value => $$invalidate(15, $strings = $$value));
    	let { $$slots: slots = {}, $$scope } = $$props;
    	validate_slots('BackNextButtonsRow', slots, []);
    	const dispatch = createEventDispatcher();
    	let { backText = $strings.back } = $$props;
    	let { backDisabled = false } = $$props;
    	let { backTitle = "" } = $$props;
    	let { backVisible = false } = $$props;
    	let { skipText = $strings.skip } = $$props;
    	let { skipDisabled = false } = $$props;
    	let { skipTitle = "" } = $$props;
    	let { skipVisible = false } = $$props;
    	let { nextText = $strings.next } = $$props;
    	let { nextDisabled = false } = $$props;
    	let { nextTitle = "" } = $$props;

    	const writable_props = [
    		'backText',
    		'backDisabled',
    		'backTitle',
    		'backVisible',
    		'skipText',
    		'skipDisabled',
    		'skipTitle',
    		'skipVisible',
    		'nextText',
    		'nextDisabled',
    		'nextTitle'
    	];

    	Object.keys($$props).forEach(key => {
    		if (!~writable_props.indexOf(key) && key.slice(0, 2) !== '$$' && key !== 'slot') console.warn(`<BackNextButtonsRow> was created with unknown prop '${key}'`);
    	});

    	const click_handler = () => dispatch('back');
    	const click_handler_1 = () => dispatch('skip');
    	const click_handler_2 = () => dispatch('next');

    	$$self.$$set = $$props => {
    		if ('backText' in $$props) $$invalidate(0, backText = $$props.backText);
    		if ('backDisabled' in $$props) $$invalidate(1, backDisabled = $$props.backDisabled);
    		if ('backTitle' in $$props) $$invalidate(2, backTitle = $$props.backTitle);
    		if ('backVisible' in $$props) $$invalidate(3, backVisible = $$props.backVisible);
    		if ('skipText' in $$props) $$invalidate(4, skipText = $$props.skipText);
    		if ('skipDisabled' in $$props) $$invalidate(5, skipDisabled = $$props.skipDisabled);
    		if ('skipTitle' in $$props) $$invalidate(6, skipTitle = $$props.skipTitle);
    		if ('skipVisible' in $$props) $$invalidate(7, skipVisible = $$props.skipVisible);
    		if ('nextText' in $$props) $$invalidate(8, nextText = $$props.nextText);
    		if ('nextDisabled' in $$props) $$invalidate(9, nextDisabled = $$props.nextDisabled);
    		if ('nextTitle' in $$props) $$invalidate(10, nextTitle = $$props.nextTitle);
    	};

    	$$self.$capture_state = () => ({
    		createEventDispatcher,
    		strings,
    		Button,
    		dispatch,
    		backText,
    		backDisabled,
    		backTitle,
    		backVisible,
    		skipText,
    		skipDisabled,
    		skipTitle,
    		skipVisible,
    		nextText,
    		nextDisabled,
    		nextTitle,
    		$strings
    	});

    	$$self.$inject_state = $$props => {
    		if ('backText' in $$props) $$invalidate(0, backText = $$props.backText);
    		if ('backDisabled' in $$props) $$invalidate(1, backDisabled = $$props.backDisabled);
    		if ('backTitle' in $$props) $$invalidate(2, backTitle = $$props.backTitle);
    		if ('backVisible' in $$props) $$invalidate(3, backVisible = $$props.backVisible);
    		if ('skipText' in $$props) $$invalidate(4, skipText = $$props.skipText);
    		if ('skipDisabled' in $$props) $$invalidate(5, skipDisabled = $$props.skipDisabled);
    		if ('skipTitle' in $$props) $$invalidate(6, skipTitle = $$props.skipTitle);
    		if ('skipVisible' in $$props) $$invalidate(7, skipVisible = $$props.skipVisible);
    		if ('nextText' in $$props) $$invalidate(8, nextText = $$props.nextText);
    		if ('nextDisabled' in $$props) $$invalidate(9, nextDisabled = $$props.nextDisabled);
    		if ('nextTitle' in $$props) $$invalidate(10, nextTitle = $$props.nextTitle);
    	};

    	if ($$props && "$$inject" in $$props) {
    		$$self.$inject_state($$props.$$inject);
    	}

    	return [
    		backText,
    		backDisabled,
    		backTitle,
    		backVisible,
    		skipText,
    		skipDisabled,
    		skipTitle,
    		skipVisible,
    		nextText,
    		nextDisabled,
    		nextTitle,
    		dispatch,
    		click_handler,
    		click_handler_1,
    		click_handler_2
    	];
    }

    class BackNextButtonsRow extends SvelteComponentDev {
    	constructor(options) {
    		super(options);

    		init(this, options, instance$o, create_fragment$o, safe_not_equal, {
    			backText: 0,
    			backDisabled: 1,
    			backTitle: 2,
    			backVisible: 3,
    			skipText: 4,
    			skipDisabled: 5,
    			skipTitle: 6,
    			skipVisible: 7,
    			nextText: 8,
    			nextDisabled: 9,
    			nextTitle: 10
    		});

    		dispatch_dev("SvelteRegisterComponent", {
    			component: this,
    			tagName: "BackNextButtonsRow",
    			options,
    			id: create_fragment$o.name
    		});
    	}

    	get backText() {
    		throw new Error("<BackNextButtonsRow>: Props cannot be read directly from the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
    	}

    	set backText(value) {
    		throw new Error("<BackNextButtonsRow>: Props cannot be set directly on the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
    	}

    	get backDisabled() {
    		throw new Error("<BackNextButtonsRow>: Props cannot be read directly from the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
    	}

    	set backDisabled(value) {
    		throw new Error("<BackNextButtonsRow>: Props cannot be set directly on the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
    	}

    	get backTitle() {
    		throw new Error("<BackNextButtonsRow>: Props cannot be read directly from the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
    	}

    	set backTitle(value) {
    		throw new Error("<BackNextButtonsRow>: Props cannot be set directly on the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
    	}

    	get backVisible() {
    		throw new Error("<BackNextButtonsRow>: Props cannot be read directly from the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
    	}

    	set backVisible(value) {
    		throw new Error("<BackNextButtonsRow>: Props cannot be set directly on the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
    	}

    	get skipText() {
    		throw new Error("<BackNextButtonsRow>: Props cannot be read directly from the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
    	}

    	set skipText(value) {
    		throw new Error("<BackNextButtonsRow>: Props cannot be set directly on the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
    	}

    	get skipDisabled() {
    		throw new Error("<BackNextButtonsRow>: Props cannot be read directly from the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
    	}

    	set skipDisabled(value) {
    		throw new Error("<BackNextButtonsRow>: Props cannot be set directly on the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
    	}

    	get skipTitle() {
    		throw new Error("<BackNextButtonsRow>: Props cannot be read directly from the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
    	}

    	set skipTitle(value) {
    		throw new Error("<BackNextButtonsRow>: Props cannot be set directly on the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
    	}

    	get skipVisible() {
    		throw new Error("<BackNextButtonsRow>: Props cannot be read directly from the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
    	}

    	set skipVisible(value) {
    		throw new Error("<BackNextButtonsRow>: Props cannot be set directly on the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
    	}

    	get nextText() {
    		throw new Error("<BackNextButtonsRow>: Props cannot be read directly from the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
    	}

    	set nextText(value) {
    		throw new Error("<BackNextButtonsRow>: Props cannot be set directly on the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
    	}

    	get nextDisabled() {
    		throw new Error("<BackNextButtonsRow>: Props cannot be read directly from the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
    	}

    	set nextDisabled(value) {
    		throw new Error("<BackNextButtonsRow>: Props cannot be set directly on the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
    	}

    	get nextTitle() {
    		throw new Error("<BackNextButtonsRow>: Props cannot be read directly from the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
    	}

    	set nextTitle(value) {
    		throw new Error("<BackNextButtonsRow>: Props cannot be set directly on the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
    	}
    }

    /* src/amazon-s3-and-cloudfront/ui/components/KeyFileDefine.svelte generated by Svelte v3.49.0 */

    const file$l = "src/amazon-s3-and-cloudfront/ui/components/KeyFileDefine.svelte";

    function create_fragment$n(ctx) {
    	let p;
    	let raw_value = /*provider*/ ctx[0].define_key_file_desc + "";
    	let t0;
    	let pre;
    	let t1_value = /*provider*/ ctx[0].define_key_file_example + "";
    	let t1;

    	const block = {
    		c: function create() {
    			p = element("p");
    			t0 = space();
    			pre = element("pre");
    			t1 = text(t1_value);
    			add_location(p, file$l, 4, 0, 42);
    			add_location(pre, file$l, 6, 0, 88);
    		},
    		l: function claim(nodes) {
    			throw new Error("options.hydrate only works if the component was compiled with the `hydratable: true` option");
    		},
    		m: function mount(target, anchor) {
    			insert_dev(target, p, anchor);
    			p.innerHTML = raw_value;
    			insert_dev(target, t0, anchor);
    			insert_dev(target, pre, anchor);
    			append_dev(pre, t1);
    		},
    		p: function update(ctx, [dirty]) {
    			if (dirty & /*provider*/ 1 && raw_value !== (raw_value = /*provider*/ ctx[0].define_key_file_desc + "")) p.innerHTML = raw_value;			if (dirty & /*provider*/ 1 && t1_value !== (t1_value = /*provider*/ ctx[0].define_key_file_example + "")) set_data_dev(t1, t1_value);
    		},
    		i: noop,
    		o: noop,
    		d: function destroy(detaching) {
    			if (detaching) detach_dev(p);
    			if (detaching) detach_dev(t0);
    			if (detaching) detach_dev(pre);
    		}
    	};

    	dispatch_dev("SvelteRegisterBlock", {
    		block,
    		id: create_fragment$n.name,
    		type: "component",
    		source: "",
    		ctx
    	});

    	return block;
    }

    function instance$n($$self, $$props, $$invalidate) {
    	let { $$slots: slots = {}, $$scope } = $$props;
    	validate_slots('KeyFileDefine', slots, []);
    	let { provider } = $$props;
    	const writable_props = ['provider'];

    	Object.keys($$props).forEach(key => {
    		if (!~writable_props.indexOf(key) && key.slice(0, 2) !== '$$' && key !== 'slot') console.warn(`<KeyFileDefine> was created with unknown prop '${key}'`);
    	});

    	$$self.$$set = $$props => {
    		if ('provider' in $$props) $$invalidate(0, provider = $$props.provider);
    	};

    	$$self.$capture_state = () => ({ provider });

    	$$self.$inject_state = $$props => {
    		if ('provider' in $$props) $$invalidate(0, provider = $$props.provider);
    	};

    	if ($$props && "$$inject" in $$props) {
    		$$self.$inject_state($$props.$$inject);
    	}

    	return [provider];
    }

    class KeyFileDefine extends SvelteComponentDev {
    	constructor(options) {
    		super(options);
    		init(this, options, instance$n, create_fragment$n, safe_not_equal, { provider: 0 });

    		dispatch_dev("SvelteRegisterComponent", {
    			component: this,
    			tagName: "KeyFileDefine",
    			options,
    			id: create_fragment$n.name
    		});

    		const { ctx } = this.$$;
    		const props = options.props || {};

    		if (/*provider*/ ctx[0] === undefined && !('provider' in props)) {
    			console.warn("<KeyFileDefine> was created without expected prop 'provider'");
    		}
    	}

    	get provider() {
    		throw new Error("<KeyFileDefine>: Props cannot be read directly from the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
    	}

    	set provider(value) {
    		throw new Error("<KeyFileDefine>: Props cannot be set directly on the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
    	}
    }

    /* src/amazon-s3-and-cloudfront/ui/components/UseServerRolesDefine.svelte generated by Svelte v3.49.0 */

    const file$k = "src/amazon-s3-and-cloudfront/ui/components/UseServerRolesDefine.svelte";

    function create_fragment$m(ctx) {
    	let p;
    	let raw_value = /*provider*/ ctx[0].use_server_roles_desc + "";
    	let t0;
    	let pre;
    	let t1_value = /*provider*/ ctx[0].use_server_roles_example + "";
    	let t1;

    	const block = {
    		c: function create() {
    			p = element("p");
    			t0 = space();
    			pre = element("pre");
    			t1 = text(t1_value);
    			add_location(p, file$k, 4, 0, 42);
    			add_location(pre, file$k, 6, 0, 89);
    		},
    		l: function claim(nodes) {
    			throw new Error("options.hydrate only works if the component was compiled with the `hydratable: true` option");
    		},
    		m: function mount(target, anchor) {
    			insert_dev(target, p, anchor);
    			p.innerHTML = raw_value;
    			insert_dev(target, t0, anchor);
    			insert_dev(target, pre, anchor);
    			append_dev(pre, t1);
    		},
    		p: function update(ctx, [dirty]) {
    			if (dirty & /*provider*/ 1 && raw_value !== (raw_value = /*provider*/ ctx[0].use_server_roles_desc + "")) p.innerHTML = raw_value;			if (dirty & /*provider*/ 1 && t1_value !== (t1_value = /*provider*/ ctx[0].use_server_roles_example + "")) set_data_dev(t1, t1_value);
    		},
    		i: noop,
    		o: noop,
    		d: function destroy(detaching) {
    			if (detaching) detach_dev(p);
    			if (detaching) detach_dev(t0);
    			if (detaching) detach_dev(pre);
    		}
    	};

    	dispatch_dev("SvelteRegisterBlock", {
    		block,
    		id: create_fragment$m.name,
    		type: "component",
    		source: "",
    		ctx
    	});

    	return block;
    }

    function instance$m($$self, $$props, $$invalidate) {
    	let { $$slots: slots = {}, $$scope } = $$props;
    	validate_slots('UseServerRolesDefine', slots, []);
    	let { provider } = $$props;
    	const writable_props = ['provider'];

    	Object.keys($$props).forEach(key => {
    		if (!~writable_props.indexOf(key) && key.slice(0, 2) !== '$$' && key !== 'slot') console.warn(`<UseServerRolesDefine> was created with unknown prop '${key}'`);
    	});

    	$$self.$$set = $$props => {
    		if ('provider' in $$props) $$invalidate(0, provider = $$props.provider);
    	};

    	$$self.$capture_state = () => ({ provider });

    	$$self.$inject_state = $$props => {
    		if ('provider' in $$props) $$invalidate(0, provider = $$props.provider);
    	};

    	if ($$props && "$$inject" in $$props) {
    		$$self.$inject_state($$props.$$inject);
    	}

    	return [provider];
    }

    class UseServerRolesDefine extends SvelteComponentDev {
    	constructor(options) {
    		super(options);
    		init(this, options, instance$m, create_fragment$m, safe_not_equal, { provider: 0 });

    		dispatch_dev("SvelteRegisterComponent", {
    			component: this,
    			tagName: "UseServerRolesDefine",
    			options,
    			id: create_fragment$m.name
    		});

    		const { ctx } = this.$$;
    		const props = options.props || {};

    		if (/*provider*/ ctx[0] === undefined && !('provider' in props)) {
    			console.warn("<UseServerRolesDefine> was created without expected prop 'provider'");
    		}
    	}

    	get provider() {
    		throw new Error("<UseServerRolesDefine>: Props cannot be read directly from the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
    	}

    	set provider(value) {
    		throw new Error("<UseServerRolesDefine>: Props cannot be set directly on the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
    	}
    }

    /* src/amazon-s3-and-cloudfront/ui/components/AccessKeysEntry.svelte generated by Svelte v3.49.0 */
    const file$j = "src/amazon-s3-and-cloudfront/ui/components/AccessKeysEntry.svelte";

    function create_fragment$l(ctx) {
    	let p;
    	let raw_value = /*provider*/ ctx[2].enter_access_keys_desc + "";
    	let t0;
    	let label0;
    	let t1;
    	let t2;
    	let input0;
    	let t3;
    	let label1;
    	let t4;
    	let t5;
    	let input1;
    	let mounted;
    	let dispose;

    	const block = {
    		c: function create() {
    			p = element("p");
    			t0 = space();
    			label0 = element("label");
    			t1 = text(/*accessKeyIdLabel*/ ctx[5]);
    			t2 = space();
    			input0 = element("input");
    			t3 = space();
    			label1 = element("label");
    			t4 = text(/*secretAccessKeyLabel*/ ctx[7]);
    			t5 = space();
    			input1 = element("input");
    			add_location(p, file$j, 15, 0, 370);
    			attr_dev(label0, "class", "input-label");
    			attr_dev(label0, "for", /*accessKeyIdName*/ ctx[4]);
    			add_location(label0, file$j, 17, 0, 418);
    			attr_dev(input0, "type", "text");
    			attr_dev(input0, "id", /*accessKeyIdName*/ ctx[4]);
    			attr_dev(input0, "name", /*accessKeyIdName*/ ctx[4]);
    			attr_dev(input0, "minlength", "20");
    			attr_dev(input0, "size", "20");
    			input0.disabled = /*disabled*/ ctx[3];
    			toggle_class(input0, "disabled", /*disabled*/ ctx[3]);
    			add_location(input0, file$j, 18, 0, 494);
    			attr_dev(label1, "class", "input-label");
    			attr_dev(label1, "for", /*secretAccessKeyName*/ ctx[6]);
    			add_location(label1, file$j, 29, 0, 644);
    			attr_dev(input1, "type", "text");
    			attr_dev(input1, "id", /*secretAccessKeyName*/ ctx[6]);
    			attr_dev(input1, "name", /*secretAccessKeyName*/ ctx[6]);
    			attr_dev(input1, "autocomplete", "off");
    			attr_dev(input1, "minlength", "40");
    			attr_dev(input1, "size", "40");
    			input1.disabled = /*disabled*/ ctx[3];
    			toggle_class(input1, "disabled", /*disabled*/ ctx[3]);
    			add_location(input1, file$j, 30, 0, 728);
    		},
    		l: function claim(nodes) {
    			throw new Error("options.hydrate only works if the component was compiled with the `hydratable: true` option");
    		},
    		m: function mount(target, anchor) {
    			insert_dev(target, p, anchor);
    			p.innerHTML = raw_value;
    			insert_dev(target, t0, anchor);
    			insert_dev(target, label0, anchor);
    			append_dev(label0, t1);
    			insert_dev(target, t2, anchor);
    			insert_dev(target, input0, anchor);
    			set_input_value(input0, /*accessKeyId*/ ctx[0]);
    			insert_dev(target, t3, anchor);
    			insert_dev(target, label1, anchor);
    			append_dev(label1, t4);
    			insert_dev(target, t5, anchor);
    			insert_dev(target, input1, anchor);
    			set_input_value(input1, /*secretAccessKey*/ ctx[1]);

    			if (!mounted) {
    				dispose = [
    					listen_dev(input0, "input", /*input0_input_handler*/ ctx[8]),
    					listen_dev(input1, "input", /*input1_input_handler*/ ctx[9])
    				];

    				mounted = true;
    			}
    		},
    		p: function update(ctx, [dirty]) {
    			if (dirty & /*provider*/ 4 && raw_value !== (raw_value = /*provider*/ ctx[2].enter_access_keys_desc + "")) p.innerHTML = raw_value;
    			if (dirty & /*disabled*/ 8) {
    				prop_dev(input0, "disabled", /*disabled*/ ctx[3]);
    			}

    			if (dirty & /*accessKeyId*/ 1 && input0.value !== /*accessKeyId*/ ctx[0]) {
    				set_input_value(input0, /*accessKeyId*/ ctx[0]);
    			}

    			if (dirty & /*disabled*/ 8) {
    				toggle_class(input0, "disabled", /*disabled*/ ctx[3]);
    			}

    			if (dirty & /*disabled*/ 8) {
    				prop_dev(input1, "disabled", /*disabled*/ ctx[3]);
    			}

    			if (dirty & /*secretAccessKey*/ 2 && input1.value !== /*secretAccessKey*/ ctx[1]) {
    				set_input_value(input1, /*secretAccessKey*/ ctx[1]);
    			}

    			if (dirty & /*disabled*/ 8) {
    				toggle_class(input1, "disabled", /*disabled*/ ctx[3]);
    			}
    		},
    		i: noop,
    		o: noop,
    		d: function destroy(detaching) {
    			if (detaching) detach_dev(p);
    			if (detaching) detach_dev(t0);
    			if (detaching) detach_dev(label0);
    			if (detaching) detach_dev(t2);
    			if (detaching) detach_dev(input0);
    			if (detaching) detach_dev(t3);
    			if (detaching) detach_dev(label1);
    			if (detaching) detach_dev(t5);
    			if (detaching) detach_dev(input1);
    			mounted = false;
    			run_all(dispose);
    		}
    	};

    	dispatch_dev("SvelteRegisterBlock", {
    		block,
    		id: create_fragment$l.name,
    		type: "component",
    		source: "",
    		ctx
    	});

    	return block;
    }

    function instance$l($$self, $$props, $$invalidate) {
    	let $strings;
    	validate_store(strings, 'strings');
    	component_subscribe($$self, strings, $$value => $$invalidate(10, $strings = $$value));
    	let { $$slots: slots = {}, $$scope } = $$props;
    	validate_slots('AccessKeysEntry', slots, []);
    	let { provider } = $$props;
    	let { accessKeyId = "" } = $$props;
    	let { secretAccessKey = "" } = $$props;
    	let { disabled = false } = $$props;
    	let accessKeyIdName = "access-key-id";
    	let accessKeyIdLabel = $strings.access_key_id;
    	let secretAccessKeyName = "secret-access-key";
    	let secretAccessKeyLabel = $strings.secret_access_key;
    	const writable_props = ['provider', 'accessKeyId', 'secretAccessKey', 'disabled'];

    	Object.keys($$props).forEach(key => {
    		if (!~writable_props.indexOf(key) && key.slice(0, 2) !== '$$' && key !== 'slot') console.warn(`<AccessKeysEntry> was created with unknown prop '${key}'`);
    	});

    	function input0_input_handler() {
    		accessKeyId = this.value;
    		$$invalidate(0, accessKeyId);
    	}

    	function input1_input_handler() {
    		secretAccessKey = this.value;
    		$$invalidate(1, secretAccessKey);
    	}

    	$$self.$$set = $$props => {
    		if ('provider' in $$props) $$invalidate(2, provider = $$props.provider);
    		if ('accessKeyId' in $$props) $$invalidate(0, accessKeyId = $$props.accessKeyId);
    		if ('secretAccessKey' in $$props) $$invalidate(1, secretAccessKey = $$props.secretAccessKey);
    		if ('disabled' in $$props) $$invalidate(3, disabled = $$props.disabled);
    	};

    	$$self.$capture_state = () => ({
    		strings,
    		provider,
    		accessKeyId,
    		secretAccessKey,
    		disabled,
    		accessKeyIdName,
    		accessKeyIdLabel,
    		secretAccessKeyName,
    		secretAccessKeyLabel,
    		$strings
    	});

    	$$self.$inject_state = $$props => {
    		if ('provider' in $$props) $$invalidate(2, provider = $$props.provider);
    		if ('accessKeyId' in $$props) $$invalidate(0, accessKeyId = $$props.accessKeyId);
    		if ('secretAccessKey' in $$props) $$invalidate(1, secretAccessKey = $$props.secretAccessKey);
    		if ('disabled' in $$props) $$invalidate(3, disabled = $$props.disabled);
    		if ('accessKeyIdName' in $$props) $$invalidate(4, accessKeyIdName = $$props.accessKeyIdName);
    		if ('accessKeyIdLabel' in $$props) $$invalidate(5, accessKeyIdLabel = $$props.accessKeyIdLabel);
    		if ('secretAccessKeyName' in $$props) $$invalidate(6, secretAccessKeyName = $$props.secretAccessKeyName);
    		if ('secretAccessKeyLabel' in $$props) $$invalidate(7, secretAccessKeyLabel = $$props.secretAccessKeyLabel);
    	};

    	if ($$props && "$$inject" in $$props) {
    		$$self.$inject_state($$props.$$inject);
    	}

    	return [
    		accessKeyId,
    		secretAccessKey,
    		provider,
    		disabled,
    		accessKeyIdName,
    		accessKeyIdLabel,
    		secretAccessKeyName,
    		secretAccessKeyLabel,
    		input0_input_handler,
    		input1_input_handler
    	];
    }

    class AccessKeysEntry extends SvelteComponentDev {
    	constructor(options) {
    		super(options);

    		init(this, options, instance$l, create_fragment$l, safe_not_equal, {
    			provider: 2,
    			accessKeyId: 0,
    			secretAccessKey: 1,
    			disabled: 3
    		});

    		dispatch_dev("SvelteRegisterComponent", {
    			component: this,
    			tagName: "AccessKeysEntry",
    			options,
    			id: create_fragment$l.name
    		});

    		const { ctx } = this.$$;
    		const props = options.props || {};

    		if (/*provider*/ ctx[2] === undefined && !('provider' in props)) {
    			console.warn("<AccessKeysEntry> was created without expected prop 'provider'");
    		}
    	}

    	get provider() {
    		throw new Error("<AccessKeysEntry>: Props cannot be read directly from the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
    	}

    	set provider(value) {
    		throw new Error("<AccessKeysEntry>: Props cannot be set directly on the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
    	}

    	get accessKeyId() {
    		throw new Error("<AccessKeysEntry>: Props cannot be read directly from the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
    	}

    	set accessKeyId(value) {
    		throw new Error("<AccessKeysEntry>: Props cannot be set directly on the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
    	}

    	get secretAccessKey() {
    		throw new Error("<AccessKeysEntry>: Props cannot be read directly from the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
    	}

    	set secretAccessKey(value) {
    		throw new Error("<AccessKeysEntry>: Props cannot be set directly on the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
    	}

    	get disabled() {
    		throw new Error("<AccessKeysEntry>: Props cannot be read directly from the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
    	}

    	set disabled(value) {
    		throw new Error("<AccessKeysEntry>: Props cannot be set directly on the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
    	}
    }

    /* src/amazon-s3-and-cloudfront/ui/components/KeyFileEntry.svelte generated by Svelte v3.49.0 */
    const file$i = "src/amazon-s3-and-cloudfront/ui/components/KeyFileEntry.svelte";

    function create_fragment$k(ctx) {
    	let p;
    	let raw_value = /*provider*/ ctx[1].enter_key_file_desc + "";
    	let t0;
    	let label_1;
    	let t1;
    	let t2;
    	let textarea;
    	let mounted;
    	let dispose;

    	const block = {
    		c: function create() {
    			p = element("p");
    			t0 = space();
    			label_1 = element("label");
    			t1 = text(/*label*/ ctx[4]);
    			t2 = space();
    			textarea = element("textarea");
    			add_location(p, file$i, 11, 0, 193);
    			attr_dev(label_1, "class", "input-label");
    			attr_dev(label_1, "for", /*name*/ ctx[3]);
    			add_location(label_1, file$i, 13, 0, 238);
    			attr_dev(textarea, "id", /*name*/ ctx[3]);
    			attr_dev(textarea, "name", /*name*/ ctx[3]);
    			textarea.disabled = /*disabled*/ ctx[2];
    			attr_dev(textarea, "rows", "10");
    			toggle_class(textarea, "disabled", /*disabled*/ ctx[2]);
    			add_location(textarea, file$i, 14, 0, 292);
    		},
    		l: function claim(nodes) {
    			throw new Error("options.hydrate only works if the component was compiled with the `hydratable: true` option");
    		},
    		m: function mount(target, anchor) {
    			insert_dev(target, p, anchor);
    			p.innerHTML = raw_value;
    			insert_dev(target, t0, anchor);
    			insert_dev(target, label_1, anchor);
    			append_dev(label_1, t1);
    			insert_dev(target, t2, anchor);
    			insert_dev(target, textarea, anchor);
    			set_input_value(textarea, /*value*/ ctx[0]);

    			if (!mounted) {
    				dispose = listen_dev(textarea, "input", /*textarea_input_handler*/ ctx[5]);
    				mounted = true;
    			}
    		},
    		p: function update(ctx, [dirty]) {
    			if (dirty & /*provider*/ 2 && raw_value !== (raw_value = /*provider*/ ctx[1].enter_key_file_desc + "")) p.innerHTML = raw_value;
    			if (dirty & /*disabled*/ 4) {
    				prop_dev(textarea, "disabled", /*disabled*/ ctx[2]);
    			}

    			if (dirty & /*value*/ 1) {
    				set_input_value(textarea, /*value*/ ctx[0]);
    			}

    			if (dirty & /*disabled*/ 4) {
    				toggle_class(textarea, "disabled", /*disabled*/ ctx[2]);
    			}
    		},
    		i: noop,
    		o: noop,
    		d: function destroy(detaching) {
    			if (detaching) detach_dev(p);
    			if (detaching) detach_dev(t0);
    			if (detaching) detach_dev(label_1);
    			if (detaching) detach_dev(t2);
    			if (detaching) detach_dev(textarea);
    			mounted = false;
    			dispose();
    		}
    	};

    	dispatch_dev("SvelteRegisterBlock", {
    		block,
    		id: create_fragment$k.name,
    		type: "component",
    		source: "",
    		ctx
    	});

    	return block;
    }

    function instance$k($$self, $$props, $$invalidate) {
    	let $strings;
    	validate_store(strings, 'strings');
    	component_subscribe($$self, strings, $$value => $$invalidate(6, $strings = $$value));
    	let { $$slots: slots = {}, $$scope } = $$props;
    	validate_slots('KeyFileEntry', slots, []);
    	let { provider } = $$props;
    	let { value = "" } = $$props;
    	let { disabled = false } = $$props;
    	let name = "key-file";
    	let label = $strings.key_file;
    	const writable_props = ['provider', 'value', 'disabled'];

    	Object.keys($$props).forEach(key => {
    		if (!~writable_props.indexOf(key) && key.slice(0, 2) !== '$$' && key !== 'slot') console.warn(`<KeyFileEntry> was created with unknown prop '${key}'`);
    	});

    	function textarea_input_handler() {
    		value = this.value;
    		$$invalidate(0, value);
    	}

    	$$self.$$set = $$props => {
    		if ('provider' in $$props) $$invalidate(1, provider = $$props.provider);
    		if ('value' in $$props) $$invalidate(0, value = $$props.value);
    		if ('disabled' in $$props) $$invalidate(2, disabled = $$props.disabled);
    	};

    	$$self.$capture_state = () => ({
    		strings,
    		provider,
    		value,
    		disabled,
    		name,
    		label,
    		$strings
    	});

    	$$self.$inject_state = $$props => {
    		if ('provider' in $$props) $$invalidate(1, provider = $$props.provider);
    		if ('value' in $$props) $$invalidate(0, value = $$props.value);
    		if ('disabled' in $$props) $$invalidate(2, disabled = $$props.disabled);
    		if ('name' in $$props) $$invalidate(3, name = $$props.name);
    		if ('label' in $$props) $$invalidate(4, label = $$props.label);
    	};

    	if ($$props && "$$inject" in $$props) {
    		$$self.$inject_state($$props.$$inject);
    	}

    	return [value, provider, disabled, name, label, textarea_input_handler];
    }

    class KeyFileEntry extends SvelteComponentDev {
    	constructor(options) {
    		super(options);
    		init(this, options, instance$k, create_fragment$k, safe_not_equal, { provider: 1, value: 0, disabled: 2 });

    		dispatch_dev("SvelteRegisterComponent", {
    			component: this,
    			tagName: "KeyFileEntry",
    			options,
    			id: create_fragment$k.name
    		});

    		const { ctx } = this.$$;
    		const props = options.props || {};

    		if (/*provider*/ ctx[1] === undefined && !('provider' in props)) {
    			console.warn("<KeyFileEntry> was created without expected prop 'provider'");
    		}
    	}

    	get provider() {
    		throw new Error("<KeyFileEntry>: Props cannot be read directly from the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
    	}

    	set provider(value) {
    		throw new Error("<KeyFileEntry>: Props cannot be set directly on the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
    	}

    	get value() {
    		throw new Error("<KeyFileEntry>: Props cannot be read directly from the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
    	}

    	set value(value) {
    		throw new Error("<KeyFileEntry>: Props cannot be set directly on the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
    	}

    	get disabled() {
    		throw new Error("<KeyFileEntry>: Props cannot be read directly from the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
    	}

    	set disabled(value) {
    		throw new Error("<KeyFileEntry>: Props cannot be set directly on the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
    	}
    }

    /* src/amazon-s3-and-cloudfront/ui/components/StorageProviderSubPage.svelte generated by Svelte v3.49.0 */

    const { Object: Object_1$2 } = globals;
    const file$h = "src/amazon-s3-and-cloudfront/ui/components/StorageProviderSubPage.svelte";

    function get_each_context$4(ctx, list, i) {
    	const child_ctx = ctx.slice();
    	child_ctx[41] = list[i];
    	return child_ctx;
    }

    // (204:1) {#if changedWithOffloaded}
    function create_if_block_11(ctx) {
    	let notification;
    	let current;

    	notification = new Notification({
    			props: {
    				inline: true,
    				warning: true,
    				heading: /*storageProvider*/ ctx[0].media_already_offloaded_warning.heading,
    				$$slots: { default: [create_default_slot_12] },
    				$$scope: { ctx }
    			},
    			$$inline: true
    		});

    	const block = {
    		c: function create() {
    			create_component(notification.$$.fragment);
    		},
    		m: function mount(target, anchor) {
    			mount_component(notification, target, anchor);
    			current = true;
    		},
    		p: function update(ctx, dirty) {
    			const notification_changes = {};
    			if (dirty[0] & /*storageProvider*/ 1) notification_changes.heading = /*storageProvider*/ ctx[0].media_already_offloaded_warning.heading;

    			if (dirty[0] & /*storageProvider*/ 1 | dirty[1] & /*$$scope*/ 8192) {
    				notification_changes.$$scope = { dirty, ctx };
    			}

    			notification.$set(notification_changes);
    		},
    		i: function intro(local) {
    			if (current) return;
    			transition_in(notification.$$.fragment, local);
    			current = true;
    		},
    		o: function outro(local) {
    			transition_out(notification.$$.fragment, local);
    			current = false;
    		},
    		d: function destroy(detaching) {
    			destroy_component(notification, detaching);
    		}
    	};

    	dispatch_dev("SvelteRegisterBlock", {
    		block,
    		id: create_if_block_11.name,
    		type: "if",
    		source: "(204:1) {#if changedWithOffloaded}",
    		ctx
    	});

    	return block;
    }

    // (205:2) <Notification inline warning heading={storageProvider.media_already_offloaded_warning.heading}>
    function create_default_slot_12(ctx) {
    	let p;
    	let raw_value = /*storageProvider*/ ctx[0].media_already_offloaded_warning.message + "";

    	const block = {
    		c: function create() {
    			p = element("p");
    			add_location(p, file$h, 205, 3, 6123);
    		},
    		m: function mount(target, anchor) {
    			insert_dev(target, p, anchor);
    			p.innerHTML = raw_value;
    		},
    		p: function update(ctx, dirty) {
    			if (dirty[0] & /*storageProvider*/ 1 && raw_value !== (raw_value = /*storageProvider*/ ctx[0].media_already_offloaded_warning.message + "")) p.innerHTML = raw_value;		},
    		d: function destroy(detaching) {
    			if (detaching) detach_dev(p);
    		}
    	};

    	dispatch_dev("SvelteRegisterBlock", {
    		block,
    		id: create_default_slot_12.name,
    		type: "slot",
    		source: "(205:2) <Notification inline warning heading={storageProvider.media_already_offloaded_warning.heading}>",
    		ctx
    	});

    	return block;
    }

    // (212:3) {#each Object.values( $storage_providers ) as provider}
    function create_each_block$4(ctx) {
    	let tabbutton;
    	let current;

    	function click_handler() {
    		return /*click_handler*/ ctx[24](/*provider*/ ctx[41]);
    	}

    	tabbutton = new TabButton({
    			props: {
    				active: /*provider*/ ctx[41].provider_key_name === /*storageProvider*/ ctx[0].provider_key_name,
    				disabled: /*disabled*/ ctx[13],
    				icon: /*provider*/ ctx[41].icon,
    				iconDesc: /*provider*/ ctx[41].icon_desc,
    				text: /*provider*/ ctx[41].provider_service_name
    			},
    			$$inline: true
    		});

    	tabbutton.$on("click", click_handler);

    	const block = {
    		c: function create() {
    			create_component(tabbutton.$$.fragment);
    		},
    		m: function mount(target, anchor) {
    			mount_component(tabbutton, target, anchor);
    			current = true;
    		},
    		p: function update(new_ctx, dirty) {
    			ctx = new_ctx;
    			const tabbutton_changes = {};
    			if (dirty[0] & /*$storage_providers, storageProvider*/ 32769) tabbutton_changes.active = /*provider*/ ctx[41].provider_key_name === /*storageProvider*/ ctx[0].provider_key_name;
    			if (dirty[0] & /*disabled*/ 8192) tabbutton_changes.disabled = /*disabled*/ ctx[13];
    			if (dirty[0] & /*$storage_providers*/ 32768) tabbutton_changes.icon = /*provider*/ ctx[41].icon;
    			if (dirty[0] & /*$storage_providers*/ 32768) tabbutton_changes.iconDesc = /*provider*/ ctx[41].icon_desc;
    			if (dirty[0] & /*$storage_providers*/ 32768) tabbutton_changes.text = /*provider*/ ctx[41].provider_service_name;
    			tabbutton.$set(tabbutton_changes);
    		},
    		i: function intro(local) {
    			if (current) return;
    			transition_in(tabbutton.$$.fragment, local);
    			current = true;
    		},
    		o: function outro(local) {
    			transition_out(tabbutton.$$.fragment, local);
    			current = false;
    		},
    		d: function destroy(detaching) {
    			destroy_component(tabbutton, detaching);
    		}
    	};

    	dispatch_dev("SvelteRegisterBlock", {
    		block,
    		id: create_each_block$4.name,
    		type: "each",
    		source: "(212:3) {#each Object.values( $storage_providers ) as provider}",
    		ctx
    	});

    	return block;
    }

    // (211:2) <PanelRow class="body flex-row tab-buttons">
    function create_default_slot_11(ctx) {
    	let t;
    	let div;
    	let p;
    	let raw_value = /*storageProvider*/ ctx[0].get_access_keys_help + "";
    	let current;
    	let each_value = Object.values(/*$storage_providers*/ ctx[15]);
    	validate_each_argument(each_value);
    	let each_blocks = [];

    	for (let i = 0; i < each_value.length; i += 1) {
    		each_blocks[i] = create_each_block$4(get_each_context$4(ctx, each_value, i));
    	}

    	const out = i => transition_out(each_blocks[i], 1, 1, () => {
    		each_blocks[i] = null;
    	});

    	const block = {
    		c: function create() {
    			for (let i = 0; i < each_blocks.length; i += 1) {
    				each_blocks[i].c();
    			}

    			t = space();
    			div = element("div");
    			p = element("p");
    			add_location(p, file$h, 223, 4, 6735);
    			attr_dev(div, "class", "notice notice-qsg");
    			add_location(div, file$h, 222, 3, 6699);
    		},
    		m: function mount(target, anchor) {
    			for (let i = 0; i < each_blocks.length; i += 1) {
    				each_blocks[i].m(target, anchor);
    			}

    			insert_dev(target, t, anchor);
    			insert_dev(target, div, anchor);
    			append_dev(div, p);
    			p.innerHTML = raw_value;
    			current = true;
    		},
    		p: function update(ctx, dirty) {
    			if (dirty[0] & /*$storage_providers, storageProvider, disabled, handleChooseProvider*/ 106497) {
    				each_value = Object.values(/*$storage_providers*/ ctx[15]);
    				validate_each_argument(each_value);
    				let i;

    				for (i = 0; i < each_value.length; i += 1) {
    					const child_ctx = get_each_context$4(ctx, each_value, i);

    					if (each_blocks[i]) {
    						each_blocks[i].p(child_ctx, dirty);
    						transition_in(each_blocks[i], 1);
    					} else {
    						each_blocks[i] = create_each_block$4(child_ctx);
    						each_blocks[i].c();
    						transition_in(each_blocks[i], 1);
    						each_blocks[i].m(t.parentNode, t);
    					}
    				}

    				group_outros();

    				for (i = each_value.length; i < each_blocks.length; i += 1) {
    					out(i);
    				}

    				check_outros();
    			}

    			if ((!current || dirty[0] & /*storageProvider*/ 1) && raw_value !== (raw_value = /*storageProvider*/ ctx[0].get_access_keys_help + "")) p.innerHTML = raw_value;		},
    		i: function intro(local) {
    			if (current) return;

    			for (let i = 0; i < each_value.length; i += 1) {
    				transition_in(each_blocks[i]);
    			}

    			current = true;
    		},
    		o: function outro(local) {
    			each_blocks = each_blocks.filter(Boolean);

    			for (let i = 0; i < each_blocks.length; i += 1) {
    				transition_out(each_blocks[i]);
    			}

    			current = false;
    		},
    		d: function destroy(detaching) {
    			destroy_each(each_blocks, detaching);
    			if (detaching) detach_dev(t);
    			if (detaching) detach_dev(div);
    		}
    	};

    	dispatch_dev("SvelteRegisterBlock", {
    		block,
    		id: create_default_slot_11.name,
    		type: "slot",
    		source: "(211:2) <PanelRow class=\\\"body flex-row tab-buttons\\\">",
    		ctx
    	});

    	return block;
    }

    // (210:1) <Panel heading={$strings.select_storage_provider_title} defined={defined} multi>
    function create_default_slot_10(ctx) {
    	let panelrow;
    	let current;

    	panelrow = new PanelRow({
    			props: {
    				class: "body flex-row tab-buttons",
    				$$slots: { default: [create_default_slot_11] },
    				$$scope: { ctx }
    			},
    			$$inline: true
    		});

    	const block = {
    		c: function create() {
    			create_component(panelrow.$$.fragment);
    		},
    		m: function mount(target, anchor) {
    			mount_component(panelrow, target, anchor);
    			current = true;
    		},
    		p: function update(ctx, dirty) {
    			const panelrow_changes = {};

    			if (dirty[0] & /*storageProvider, $storage_providers, disabled*/ 40961 | dirty[1] & /*$$scope*/ 8192) {
    				panelrow_changes.$$scope = { dirty, ctx };
    			}

    			panelrow.$set(panelrow_changes);
    		},
    		i: function intro(local) {
    			if (current) return;
    			transition_in(panelrow.$$.fragment, local);
    			current = true;
    		},
    		o: function outro(local) {
    			transition_out(panelrow.$$.fragment, local);
    			current = false;
    		},
    		d: function destroy(detaching) {
    			destroy_component(panelrow, detaching);
    		}
    	};

    	dispatch_dev("SvelteRegisterBlock", {
    		block,
    		id: create_default_slot_10.name,
    		type: "slot",
    		source: "(210:1) <Panel heading={$strings.select_storage_provider_title} defined={defined} multi>",
    		ctx
    	});

    	return block;
    }

    // (236:50) 
    function create_if_block_10(ctx) {
    	let radiobutton;
    	let updating_selected;
    	let current;

    	function radiobutton_selected_binding_1(value) {
    		/*radiobutton_selected_binding_1*/ ctx[26](value);
    	}

    	let radiobutton_props = {
    		disabled: /*authDisabled*/ ctx[11],
    		value: "define",
    		desc: /*storageProvider*/ ctx[0].defined_auth_desc,
    		$$slots: { default: [create_default_slot_9] },
    		$$scope: { ctx }
    	};

    	if (/*authMethod*/ ctx[1] !== void 0) {
    		radiobutton_props.selected = /*authMethod*/ ctx[1];
    	}

    	radiobutton = new RadioButton({ props: radiobutton_props, $$inline: true });
    	binding_callbacks.push(() => bind(radiobutton, 'selected', radiobutton_selected_binding_1));

    	const block = {
    		c: function create() {
    			create_component(radiobutton.$$.fragment);
    		},
    		m: function mount(target, anchor) {
    			mount_component(radiobutton, target, anchor);
    			current = true;
    		},
    		p: function update(ctx, dirty) {
    			const radiobutton_changes = {};
    			if (dirty[0] & /*authDisabled*/ 2048) radiobutton_changes.disabled = /*authDisabled*/ ctx[11];
    			if (dirty[0] & /*storageProvider*/ 1) radiobutton_changes.desc = /*storageProvider*/ ctx[0].defined_auth_desc;

    			if (dirty[0] & /*$strings*/ 16384 | dirty[1] & /*$$scope*/ 8192) {
    				radiobutton_changes.$$scope = { dirty, ctx };
    			}

    			if (!updating_selected && dirty[0] & /*authMethod*/ 2) {
    				updating_selected = true;
    				radiobutton_changes.selected = /*authMethod*/ ctx[1];
    				add_flush_callback(() => updating_selected = false);
    			}

    			radiobutton.$set(radiobutton_changes);
    		},
    		i: function intro(local) {
    			if (current) return;
    			transition_in(radiobutton.$$.fragment, local);
    			current = true;
    		},
    		o: function outro(local) {
    			transition_out(radiobutton.$$.fragment, local);
    			current = false;
    		},
    		d: function destroy(detaching) {
    			destroy_component(radiobutton, detaching);
    		}
    	};

    	dispatch_dev("SvelteRegisterBlock", {
    		block,
    		id: create_if_block_10.name,
    		type: "if",
    		source: "(236:50) ",
    		ctx
    	});

    	return block;
    }

    // (232:3) {#if storageProvider.use_access_keys_allowed}
    function create_if_block_9$2(ctx) {
    	let radiobutton;
    	let updating_selected;
    	let current;

    	function radiobutton_selected_binding(value) {
    		/*radiobutton_selected_binding*/ ctx[25](value);
    	}

    	let radiobutton_props = {
    		disabled: /*authDisabled*/ ctx[11],
    		value: "define",
    		desc: /*storageProvider*/ ctx[0].defined_auth_desc,
    		$$slots: { default: [create_default_slot_8$2] },
    		$$scope: { ctx }
    	};

    	if (/*authMethod*/ ctx[1] !== void 0) {
    		radiobutton_props.selected = /*authMethod*/ ctx[1];
    	}

    	radiobutton = new RadioButton({ props: radiobutton_props, $$inline: true });
    	binding_callbacks.push(() => bind(radiobutton, 'selected', radiobutton_selected_binding));

    	const block = {
    		c: function create() {
    			create_component(radiobutton.$$.fragment);
    		},
    		m: function mount(target, anchor) {
    			mount_component(radiobutton, target, anchor);
    			current = true;
    		},
    		p: function update(ctx, dirty) {
    			const radiobutton_changes = {};
    			if (dirty[0] & /*authDisabled*/ 2048) radiobutton_changes.disabled = /*authDisabled*/ ctx[11];
    			if (dirty[0] & /*storageProvider*/ 1) radiobutton_changes.desc = /*storageProvider*/ ctx[0].defined_auth_desc;

    			if (dirty[0] & /*$strings*/ 16384 | dirty[1] & /*$$scope*/ 8192) {
    				radiobutton_changes.$$scope = { dirty, ctx };
    			}

    			if (!updating_selected && dirty[0] & /*authMethod*/ 2) {
    				updating_selected = true;
    				radiobutton_changes.selected = /*authMethod*/ ctx[1];
    				add_flush_callback(() => updating_selected = false);
    			}

    			radiobutton.$set(radiobutton_changes);
    		},
    		i: function intro(local) {
    			if (current) return;
    			transition_in(radiobutton.$$.fragment, local);
    			current = true;
    		},
    		o: function outro(local) {
    			transition_out(radiobutton.$$.fragment, local);
    			current = false;
    		},
    		d: function destroy(detaching) {
    			destroy_component(radiobutton, detaching);
    		}
    	};

    	dispatch_dev("SvelteRegisterBlock", {
    		block,
    		id: create_if_block_9$2.name,
    		type: "if",
    		source: "(232:3) {#if storageProvider.use_access_keys_allowed}",
    		ctx
    	});

    	return block;
    }

    // (237:4) <RadioButton bind:selected={authMethod} disabled={authDisabled} value="define" desc={storageProvider.defined_auth_desc}>
    function create_default_slot_9(ctx) {
    	let t_value = /*$strings*/ ctx[14].define_key_file_path + "";
    	let t;

    	const block = {
    		c: function create() {
    			t = text(t_value);
    		},
    		m: function mount(target, anchor) {
    			insert_dev(target, t, anchor);
    		},
    		p: function update(ctx, dirty) {
    			if (dirty[0] & /*$strings*/ 16384 && t_value !== (t_value = /*$strings*/ ctx[14].define_key_file_path + "")) set_data_dev(t, t_value);
    		},
    		d: function destroy(detaching) {
    			if (detaching) detach_dev(t);
    		}
    	};

    	dispatch_dev("SvelteRegisterBlock", {
    		block,
    		id: create_default_slot_9.name,
    		type: "slot",
    		source: "(237:4) <RadioButton bind:selected={authMethod} disabled={authDisabled} value=\\\"define\\\" desc={storageProvider.defined_auth_desc}>",
    		ctx
    	});

    	return block;
    }

    // (233:4) <RadioButton bind:selected={authMethod} disabled={authDisabled} value="define" desc={storageProvider.defined_auth_desc}>
    function create_default_slot_8$2(ctx) {
    	let t_value = /*$strings*/ ctx[14].define_access_keys + "";
    	let t;

    	const block = {
    		c: function create() {
    			t = text(t_value);
    		},
    		m: function mount(target, anchor) {
    			insert_dev(target, t, anchor);
    		},
    		p: function update(ctx, dirty) {
    			if (dirty[0] & /*$strings*/ 16384 && t_value !== (t_value = /*$strings*/ ctx[14].define_access_keys + "")) set_data_dev(t, t_value);
    		},
    		d: function destroy(detaching) {
    			if (detaching) detach_dev(t);
    		}
    	};

    	dispatch_dev("SvelteRegisterBlock", {
    		block,
    		id: create_default_slot_8$2.name,
    		type: "slot",
    		source: "(233:4) <RadioButton bind:selected={authMethod} disabled={authDisabled} value=\\\"define\\\" desc={storageProvider.defined_auth_desc}>",
    		ctx
    	});

    	return block;
    }

    // (243:3) {#if storageProvider.use_server_roles_allowed}
    function create_if_block_8$2(ctx) {
    	let radiobutton;
    	let updating_selected;
    	let current;

    	function radiobutton_selected_binding_2(value) {
    		/*radiobutton_selected_binding_2*/ ctx[27](value);
    	}

    	let radiobutton_props = {
    		disabled: /*authDisabled*/ ctx[11],
    		value: "server-role",
    		desc: /*storageProvider*/ ctx[0].defined_auth_desc,
    		$$slots: { default: [create_default_slot_7$2] },
    		$$scope: { ctx }
    	};

    	if (/*authMethod*/ ctx[1] !== void 0) {
    		radiobutton_props.selected = /*authMethod*/ ctx[1];
    	}

    	radiobutton = new RadioButton({ props: radiobutton_props, $$inline: true });
    	binding_callbacks.push(() => bind(radiobutton, 'selected', radiobutton_selected_binding_2));

    	const block = {
    		c: function create() {
    			create_component(radiobutton.$$.fragment);
    		},
    		m: function mount(target, anchor) {
    			mount_component(radiobutton, target, anchor);
    			current = true;
    		},
    		p: function update(ctx, dirty) {
    			const radiobutton_changes = {};
    			if (dirty[0] & /*authDisabled*/ 2048) radiobutton_changes.disabled = /*authDisabled*/ ctx[11];
    			if (dirty[0] & /*storageProvider*/ 1) radiobutton_changes.desc = /*storageProvider*/ ctx[0].defined_auth_desc;

    			if (dirty[0] & /*storageProvider*/ 1 | dirty[1] & /*$$scope*/ 8192) {
    				radiobutton_changes.$$scope = { dirty, ctx };
    			}

    			if (!updating_selected && dirty[0] & /*authMethod*/ 2) {
    				updating_selected = true;
    				radiobutton_changes.selected = /*authMethod*/ ctx[1];
    				add_flush_callback(() => updating_selected = false);
    			}

    			radiobutton.$set(radiobutton_changes);
    		},
    		i: function intro(local) {
    			if (current) return;
    			transition_in(radiobutton.$$.fragment, local);
    			current = true;
    		},
    		o: function outro(local) {
    			transition_out(radiobutton.$$.fragment, local);
    			current = false;
    		},
    		d: function destroy(detaching) {
    			destroy_component(radiobutton, detaching);
    		}
    	};

    	dispatch_dev("SvelteRegisterBlock", {
    		block,
    		id: create_if_block_8$2.name,
    		type: "if",
    		source: "(243:3) {#if storageProvider.use_server_roles_allowed}",
    		ctx
    	});

    	return block;
    }

    // (244:4) <RadioButton bind:selected={authMethod} disabled={authDisabled} value="server-role" desc={storageProvider.defined_auth_desc}>
    function create_default_slot_7$2(ctx) {
    	let t_value = /*storageProvider*/ ctx[0].use_server_roles_title + "";
    	let t;

    	const block = {
    		c: function create() {
    			t = text(t_value);
    		},
    		m: function mount(target, anchor) {
    			insert_dev(target, t, anchor);
    		},
    		p: function update(ctx, dirty) {
    			if (dirty[0] & /*storageProvider*/ 1 && t_value !== (t_value = /*storageProvider*/ ctx[0].use_server_roles_title + "")) set_data_dev(t, t_value);
    		},
    		d: function destroy(detaching) {
    			if (detaching) detach_dev(t);
    		}
    	};

    	dispatch_dev("SvelteRegisterBlock", {
    		block,
    		id: create_default_slot_7$2.name,
    		type: "slot",
    		source: "(244:4) <RadioButton bind:selected={authMethod} disabled={authDisabled} value=\\\"server-role\\\" desc={storageProvider.defined_auth_desc}>",
    		ctx
    	});

    	return block;
    }

    // (254:50) 
    function create_if_block_7$2(ctx) {
    	let radiobutton;
    	let updating_selected;
    	let current;

    	function radiobutton_selected_binding_4(value) {
    		/*radiobutton_selected_binding_4*/ ctx[29](value);
    	}

    	let radiobutton_props = {
    		disabled: /*authDisabled*/ ctx[11],
    		value: "database",
    		$$slots: { default: [create_default_slot_6$2] },
    		$$scope: { ctx }
    	};

    	if (/*authMethod*/ ctx[1] !== void 0) {
    		radiobutton_props.selected = /*authMethod*/ ctx[1];
    	}

    	radiobutton = new RadioButton({ props: radiobutton_props, $$inline: true });
    	binding_callbacks.push(() => bind(radiobutton, 'selected', radiobutton_selected_binding_4));

    	const block = {
    		c: function create() {
    			create_component(radiobutton.$$.fragment);
    		},
    		m: function mount(target, anchor) {
    			mount_component(radiobutton, target, anchor);
    			current = true;
    		},
    		p: function update(ctx, dirty) {
    			const radiobutton_changes = {};
    			if (dirty[0] & /*authDisabled*/ 2048) radiobutton_changes.disabled = /*authDisabled*/ ctx[11];

    			if (dirty[0] & /*$strings*/ 16384 | dirty[1] & /*$$scope*/ 8192) {
    				radiobutton_changes.$$scope = { dirty, ctx };
    			}

    			if (!updating_selected && dirty[0] & /*authMethod*/ 2) {
    				updating_selected = true;
    				radiobutton_changes.selected = /*authMethod*/ ctx[1];
    				add_flush_callback(() => updating_selected = false);
    			}

    			radiobutton.$set(radiobutton_changes);
    		},
    		i: function intro(local) {
    			if (current) return;
    			transition_in(radiobutton.$$.fragment, local);
    			current = true;
    		},
    		o: function outro(local) {
    			transition_out(radiobutton.$$.fragment, local);
    			current = false;
    		},
    		d: function destroy(detaching) {
    			destroy_component(radiobutton, detaching);
    		}
    	};

    	dispatch_dev("SvelteRegisterBlock", {
    		block,
    		id: create_if_block_7$2.name,
    		type: "if",
    		source: "(254:50) ",
    		ctx
    	});

    	return block;
    }

    // (250:3) {#if storageProvider.use_access_keys_allowed}
    function create_if_block_6$2(ctx) {
    	let radiobutton;
    	let updating_selected;
    	let current;

    	function radiobutton_selected_binding_3(value) {
    		/*radiobutton_selected_binding_3*/ ctx[28](value);
    	}

    	let radiobutton_props = {
    		disabled: /*authDisabled*/ ctx[11],
    		value: "database",
    		$$slots: { default: [create_default_slot_5$2] },
    		$$scope: { ctx }
    	};

    	if (/*authMethod*/ ctx[1] !== void 0) {
    		radiobutton_props.selected = /*authMethod*/ ctx[1];
    	}

    	radiobutton = new RadioButton({ props: radiobutton_props, $$inline: true });
    	binding_callbacks.push(() => bind(radiobutton, 'selected', radiobutton_selected_binding_3));

    	const block = {
    		c: function create() {
    			create_component(radiobutton.$$.fragment);
    		},
    		m: function mount(target, anchor) {
    			mount_component(radiobutton, target, anchor);
    			current = true;
    		},
    		p: function update(ctx, dirty) {
    			const radiobutton_changes = {};
    			if (dirty[0] & /*authDisabled*/ 2048) radiobutton_changes.disabled = /*authDisabled*/ ctx[11];

    			if (dirty[0] & /*$strings*/ 16384 | dirty[1] & /*$$scope*/ 8192) {
    				radiobutton_changes.$$scope = { dirty, ctx };
    			}

    			if (!updating_selected && dirty[0] & /*authMethod*/ 2) {
    				updating_selected = true;
    				radiobutton_changes.selected = /*authMethod*/ ctx[1];
    				add_flush_callback(() => updating_selected = false);
    			}

    			radiobutton.$set(radiobutton_changes);
    		},
    		i: function intro(local) {
    			if (current) return;
    			transition_in(radiobutton.$$.fragment, local);
    			current = true;
    		},
    		o: function outro(local) {
    			transition_out(radiobutton.$$.fragment, local);
    			current = false;
    		},
    		d: function destroy(detaching) {
    			destroy_component(radiobutton, detaching);
    		}
    	};

    	dispatch_dev("SvelteRegisterBlock", {
    		block,
    		id: create_if_block_6$2.name,
    		type: "if",
    		source: "(250:3) {#if storageProvider.use_access_keys_allowed}",
    		ctx
    	});

    	return block;
    }

    // (255:4) <RadioButton bind:selected={authMethod} disabled={authDisabled} value="database">
    function create_default_slot_6$2(ctx) {
    	let t_value = /*$strings*/ ctx[14].store_key_file_in_db + "";
    	let t;

    	const block = {
    		c: function create() {
    			t = text(t_value);
    		},
    		m: function mount(target, anchor) {
    			insert_dev(target, t, anchor);
    		},
    		p: function update(ctx, dirty) {
    			if (dirty[0] & /*$strings*/ 16384 && t_value !== (t_value = /*$strings*/ ctx[14].store_key_file_in_db + "")) set_data_dev(t, t_value);
    		},
    		d: function destroy(detaching) {
    			if (detaching) detach_dev(t);
    		}
    	};

    	dispatch_dev("SvelteRegisterBlock", {
    		block,
    		id: create_default_slot_6$2.name,
    		type: "slot",
    		source: "(255:4) <RadioButton bind:selected={authMethod} disabled={authDisabled} value=\\\"database\\\">",
    		ctx
    	});

    	return block;
    }

    // (251:4) <RadioButton bind:selected={authMethod} disabled={authDisabled} value="database">
    function create_default_slot_5$2(ctx) {
    	let t_value = /*$strings*/ ctx[14].store_access_keys_in_db + "";
    	let t;

    	const block = {
    		c: function create() {
    			t = text(t_value);
    		},
    		m: function mount(target, anchor) {
    			insert_dev(target, t, anchor);
    		},
    		p: function update(ctx, dirty) {
    			if (dirty[0] & /*$strings*/ 16384 && t_value !== (t_value = /*$strings*/ ctx[14].store_access_keys_in_db + "")) set_data_dev(t, t_value);
    		},
    		d: function destroy(detaching) {
    			if (detaching) detach_dev(t);
    		}
    	};

    	dispatch_dev("SvelteRegisterBlock", {
    		block,
    		id: create_default_slot_5$2.name,
    		type: "slot",
    		source: "(251:4) <RadioButton bind:selected={authMethod} disabled={authDisabled} value=\\\"database\\\">",
    		ctx
    	});

    	return block;
    }

    // (230:2) <PanelRow class="body flex-column">
    function create_default_slot_4$3(ctx) {
    	let current_block_type_index;
    	let if_block0;
    	let t0;
    	let t1;
    	let current_block_type_index_1;
    	let if_block2;
    	let if_block2_anchor;
    	let current;
    	const if_block_creators = [create_if_block_9$2, create_if_block_10];
    	const if_blocks = [];

    	function select_block_type(ctx, dirty) {
    		if (/*storageProvider*/ ctx[0].use_access_keys_allowed) return 0;
    		if (/*storageProvider*/ ctx[0].use_key_file_allowed) return 1;
    		return -1;
    	}

    	if (~(current_block_type_index = select_block_type(ctx))) {
    		if_block0 = if_blocks[current_block_type_index] = if_block_creators[current_block_type_index](ctx);
    	}

    	let if_block1 = /*storageProvider*/ ctx[0].use_server_roles_allowed && create_if_block_8$2(ctx);
    	const if_block_creators_1 = [create_if_block_6$2, create_if_block_7$2];
    	const if_blocks_1 = [];

    	function select_block_type_1(ctx, dirty) {
    		if (/*storageProvider*/ ctx[0].use_access_keys_allowed) return 0;
    		if (/*storageProvider*/ ctx[0].use_key_file_allowed) return 1;
    		return -1;
    	}

    	if (~(current_block_type_index_1 = select_block_type_1(ctx))) {
    		if_block2 = if_blocks_1[current_block_type_index_1] = if_block_creators_1[current_block_type_index_1](ctx);
    	}

    	const block = {
    		c: function create() {
    			if (if_block0) if_block0.c();
    			t0 = space();
    			if (if_block1) if_block1.c();
    			t1 = space();
    			if (if_block2) if_block2.c();
    			if_block2_anchor = empty();
    		},
    		m: function mount(target, anchor) {
    			if (~current_block_type_index) {
    				if_blocks[current_block_type_index].m(target, anchor);
    			}

    			insert_dev(target, t0, anchor);
    			if (if_block1) if_block1.m(target, anchor);
    			insert_dev(target, t1, anchor);

    			if (~current_block_type_index_1) {
    				if_blocks_1[current_block_type_index_1].m(target, anchor);
    			}

    			insert_dev(target, if_block2_anchor, anchor);
    			current = true;
    		},
    		p: function update(ctx, dirty) {
    			let previous_block_index = current_block_type_index;
    			current_block_type_index = select_block_type(ctx);

    			if (current_block_type_index === previous_block_index) {
    				if (~current_block_type_index) {
    					if_blocks[current_block_type_index].p(ctx, dirty);
    				}
    			} else {
    				if (if_block0) {
    					group_outros();

    					transition_out(if_blocks[previous_block_index], 1, 1, () => {
    						if_blocks[previous_block_index] = null;
    					});

    					check_outros();
    				}

    				if (~current_block_type_index) {
    					if_block0 = if_blocks[current_block_type_index];

    					if (!if_block0) {
    						if_block0 = if_blocks[current_block_type_index] = if_block_creators[current_block_type_index](ctx);
    						if_block0.c();
    					} else {
    						if_block0.p(ctx, dirty);
    					}

    					transition_in(if_block0, 1);
    					if_block0.m(t0.parentNode, t0);
    				} else {
    					if_block0 = null;
    				}
    			}

    			if (/*storageProvider*/ ctx[0].use_server_roles_allowed) {
    				if (if_block1) {
    					if_block1.p(ctx, dirty);

    					if (dirty[0] & /*storageProvider*/ 1) {
    						transition_in(if_block1, 1);
    					}
    				} else {
    					if_block1 = create_if_block_8$2(ctx);
    					if_block1.c();
    					transition_in(if_block1, 1);
    					if_block1.m(t1.parentNode, t1);
    				}
    			} else if (if_block1) {
    				group_outros();

    				transition_out(if_block1, 1, 1, () => {
    					if_block1 = null;
    				});

    				check_outros();
    			}

    			let previous_block_index_1 = current_block_type_index_1;
    			current_block_type_index_1 = select_block_type_1(ctx);

    			if (current_block_type_index_1 === previous_block_index_1) {
    				if (~current_block_type_index_1) {
    					if_blocks_1[current_block_type_index_1].p(ctx, dirty);
    				}
    			} else {
    				if (if_block2) {
    					group_outros();

    					transition_out(if_blocks_1[previous_block_index_1], 1, 1, () => {
    						if_blocks_1[previous_block_index_1] = null;
    					});

    					check_outros();
    				}

    				if (~current_block_type_index_1) {
    					if_block2 = if_blocks_1[current_block_type_index_1];

    					if (!if_block2) {
    						if_block2 = if_blocks_1[current_block_type_index_1] = if_block_creators_1[current_block_type_index_1](ctx);
    						if_block2.c();
    					} else {
    						if_block2.p(ctx, dirty);
    					}

    					transition_in(if_block2, 1);
    					if_block2.m(if_block2_anchor.parentNode, if_block2_anchor);
    				} else {
    					if_block2 = null;
    				}
    			}
    		},
    		i: function intro(local) {
    			if (current) return;
    			transition_in(if_block0);
    			transition_in(if_block1);
    			transition_in(if_block2);
    			current = true;
    		},
    		o: function outro(local) {
    			transition_out(if_block0);
    			transition_out(if_block1);
    			transition_out(if_block2);
    			current = false;
    		},
    		d: function destroy(detaching) {
    			if (~current_block_type_index) {
    				if_blocks[current_block_type_index].d(detaching);
    			}

    			if (detaching) detach_dev(t0);
    			if (if_block1) if_block1.d(detaching);
    			if (detaching) detach_dev(t1);

    			if (~current_block_type_index_1) {
    				if_blocks_1[current_block_type_index_1].d(detaching);
    			}

    			if (detaching) detach_dev(if_block2_anchor);
    		}
    	};

    	dispatch_dev("SvelteRegisterBlock", {
    		block,
    		id: create_default_slot_4$3.name,
    		type: "slot",
    		source: "(230:2) <PanelRow class=\\\"body flex-column\\\">",
    		ctx
    	});

    	return block;
    }

    // (229:1) <Panel heading={$strings.select_auth_method_title} defined={authDefined} multi>
    function create_default_slot_3$3(ctx) {
    	let panelrow;
    	let current;

    	panelrow = new PanelRow({
    			props: {
    				class: "body flex-column",
    				$$slots: { default: [create_default_slot_4$3] },
    				$$scope: { ctx }
    			},
    			$$inline: true
    		});

    	const block = {
    		c: function create() {
    			create_component(panelrow.$$.fragment);
    		},
    		m: function mount(target, anchor) {
    			mount_component(panelrow, target, anchor);
    			current = true;
    		},
    		p: function update(ctx, dirty) {
    			const panelrow_changes = {};

    			if (dirty[0] & /*authDisabled, authMethod, $strings, storageProvider*/ 18435 | dirty[1] & /*$$scope*/ 8192) {
    				panelrow_changes.$$scope = { dirty, ctx };
    			}

    			panelrow.$set(panelrow_changes);
    		},
    		i: function intro(local) {
    			if (current) return;
    			transition_in(panelrow.$$.fragment, local);
    			current = true;
    		},
    		o: function outro(local) {
    			transition_out(panelrow.$$.fragment, local);
    			current = false;
    		},
    		d: function destroy(detaching) {
    			destroy_component(panelrow, detaching);
    		}
    	};

    	dispatch_dev("SvelteRegisterBlock", {
    		block,
    		id: create_default_slot_3$3.name,
    		type: "slot",
    		source: "(229:1) <Panel heading={$strings.select_auth_method_title} defined={authDefined} multi>",
    		ctx
    	});

    	return block;
    }

    // (262:1) {#if !authDefined}
    function create_if_block$6(ctx) {
    	let panel;
    	let current;

    	panel = new Panel({
    			props: {
    				heading: /*saveCredentialsTitle*/ ctx[10],
    				multi: true,
    				$$slots: { default: [create_default_slot_1$3] },
    				$$scope: { ctx }
    			},
    			$$inline: true
    		});

    	const block = {
    		c: function create() {
    			create_component(panel.$$.fragment);
    		},
    		m: function mount(target, anchor) {
    			mount_component(panel, target, anchor);
    			current = true;
    		},
    		p: function update(ctx, dirty) {
    			const panel_changes = {};
    			if (dirty[0] & /*saveCredentialsTitle*/ 1024) panel_changes.heading = /*saveCredentialsTitle*/ ctx[10];

    			if (dirty[0] & /*storageProvider, authMethod, authDisabled, accessKeyId, secretAccessKey, keyFile*/ 2947 | dirty[1] & /*$$scope*/ 8192) {
    				panel_changes.$$scope = { dirty, ctx };
    			}

    			panel.$set(panel_changes);
    		},
    		i: function intro(local) {
    			if (current) return;
    			transition_in(panel.$$.fragment, local);
    			current = true;
    		},
    		o: function outro(local) {
    			transition_out(panel.$$.fragment, local);
    			current = false;
    		},
    		d: function destroy(detaching) {
    			destroy_component(panel, detaching);
    		}
    	};

    	dispatch_dev("SvelteRegisterBlock", {
    		block,
    		id: create_if_block$6.name,
    		type: "if",
    		source: "(262:1) {#if !authDefined}",
    		ctx
    	});

    	return block;
    }

    // (278:80) 
    function create_if_block_5$2(ctx) {
    	let keyfileentry;
    	let updating_value;
    	let current;

    	function keyfileentry_value_binding(value) {
    		/*keyfileentry_value_binding*/ ctx[32](value);
    	}

    	let keyfileentry_props = { provider: /*storageProvider*/ ctx[0] };

    	if (/*keyFile*/ ctx[9] !== void 0) {
    		keyfileentry_props.value = /*keyFile*/ ctx[9];
    	}

    	keyfileentry = new KeyFileEntry({
    			props: keyfileentry_props,
    			$$inline: true
    		});

    	binding_callbacks.push(() => bind(keyfileentry, 'value', keyfileentry_value_binding));

    	const block = {
    		c: function create() {
    			create_component(keyfileentry.$$.fragment);
    		},
    		m: function mount(target, anchor) {
    			mount_component(keyfileentry, target, anchor);
    			current = true;
    		},
    		p: function update(ctx, dirty) {
    			const keyfileentry_changes = {};
    			if (dirty[0] & /*storageProvider*/ 1) keyfileentry_changes.provider = /*storageProvider*/ ctx[0];

    			if (!updating_value && dirty[0] & /*keyFile*/ 512) {
    				updating_value = true;
    				keyfileentry_changes.value = /*keyFile*/ ctx[9];
    				add_flush_callback(() => updating_value = false);
    			}

    			keyfileentry.$set(keyfileentry_changes);
    		},
    		i: function intro(local) {
    			if (current) return;
    			transition_in(keyfileentry.$$.fragment, local);
    			current = true;
    		},
    		o: function outro(local) {
    			transition_out(keyfileentry.$$.fragment, local);
    			current = false;
    		},
    		d: function destroy(detaching) {
    			destroy_component(keyfileentry, detaching);
    		}
    	};

    	dispatch_dev("SvelteRegisterBlock", {
    		block,
    		id: create_if_block_5$2.name,
    		type: "if",
    		source: "(278:80) ",
    		ctx
    	});

    	return block;
    }

    // (271:83) 
    function create_if_block_4$2(ctx) {
    	let accesskeysentry;
    	let updating_accessKeyId;
    	let updating_secretAccessKey;
    	let current;

    	function accesskeysentry_accessKeyId_binding(value) {
    		/*accesskeysentry_accessKeyId_binding*/ ctx[30](value);
    	}

    	function accesskeysentry_secretAccessKey_binding(value) {
    		/*accesskeysentry_secretAccessKey_binding*/ ctx[31](value);
    	}

    	let accesskeysentry_props = {
    		provider: /*storageProvider*/ ctx[0],
    		disabled: /*authDisabled*/ ctx[11]
    	};

    	if (/*accessKeyId*/ ctx[7] !== void 0) {
    		accesskeysentry_props.accessKeyId = /*accessKeyId*/ ctx[7];
    	}

    	if (/*secretAccessKey*/ ctx[8] !== void 0) {
    		accesskeysentry_props.secretAccessKey = /*secretAccessKey*/ ctx[8];
    	}

    	accesskeysentry = new AccessKeysEntry({
    			props: accesskeysentry_props,
    			$$inline: true
    		});

    	binding_callbacks.push(() => bind(accesskeysentry, 'accessKeyId', accesskeysentry_accessKeyId_binding));
    	binding_callbacks.push(() => bind(accesskeysentry, 'secretAccessKey', accesskeysentry_secretAccessKey_binding));

    	const block = {
    		c: function create() {
    			create_component(accesskeysentry.$$.fragment);
    		},
    		m: function mount(target, anchor) {
    			mount_component(accesskeysentry, target, anchor);
    			current = true;
    		},
    		p: function update(ctx, dirty) {
    			const accesskeysentry_changes = {};
    			if (dirty[0] & /*storageProvider*/ 1) accesskeysentry_changes.provider = /*storageProvider*/ ctx[0];
    			if (dirty[0] & /*authDisabled*/ 2048) accesskeysentry_changes.disabled = /*authDisabled*/ ctx[11];

    			if (!updating_accessKeyId && dirty[0] & /*accessKeyId*/ 128) {
    				updating_accessKeyId = true;
    				accesskeysentry_changes.accessKeyId = /*accessKeyId*/ ctx[7];
    				add_flush_callback(() => updating_accessKeyId = false);
    			}

    			if (!updating_secretAccessKey && dirty[0] & /*secretAccessKey*/ 256) {
    				updating_secretAccessKey = true;
    				accesskeysentry_changes.secretAccessKey = /*secretAccessKey*/ ctx[8];
    				add_flush_callback(() => updating_secretAccessKey = false);
    			}

    			accesskeysentry.$set(accesskeysentry_changes);
    		},
    		i: function intro(local) {
    			if (current) return;
    			transition_in(accesskeysentry.$$.fragment, local);
    			current = true;
    		},
    		o: function outro(local) {
    			transition_out(accesskeysentry.$$.fragment, local);
    			current = false;
    		},
    		d: function destroy(detaching) {
    			destroy_component(accesskeysentry, detaching);
    		}
    	};

    	dispatch_dev("SvelteRegisterBlock", {
    		block,
    		id: create_if_block_4$2.name,
    		type: "if",
    		source: "(271:83) ",
    		ctx
    	});

    	return block;
    }

    // (269:87) 
    function create_if_block_3$2(ctx) {
    	let useserverrolesdefine;
    	let current;

    	useserverrolesdefine = new UseServerRolesDefine({
    			props: { provider: /*storageProvider*/ ctx[0] },
    			$$inline: true
    		});

    	const block = {
    		c: function create() {
    			create_component(useserverrolesdefine.$$.fragment);
    		},
    		m: function mount(target, anchor) {
    			mount_component(useserverrolesdefine, target, anchor);
    			current = true;
    		},
    		p: function update(ctx, dirty) {
    			const useserverrolesdefine_changes = {};
    			if (dirty[0] & /*storageProvider*/ 1) useserverrolesdefine_changes.provider = /*storageProvider*/ ctx[0];
    			useserverrolesdefine.$set(useserverrolesdefine_changes);
    		},
    		i: function intro(local) {
    			if (current) return;
    			transition_in(useserverrolesdefine.$$.fragment, local);
    			current = true;
    		},
    		o: function outro(local) {
    			transition_out(useserverrolesdefine.$$.fragment, local);
    			current = false;
    		},
    		d: function destroy(detaching) {
    			destroy_component(useserverrolesdefine, detaching);
    		}
    	};

    	dispatch_dev("SvelteRegisterBlock", {
    		block,
    		id: create_if_block_3$2.name,
    		type: "if",
    		source: "(269:87) ",
    		ctx
    	});

    	return block;
    }

    // (267:78) 
    function create_if_block_2$2(ctx) {
    	let keyfiledefine;
    	let current;

    	keyfiledefine = new KeyFileDefine({
    			props: { provider: /*storageProvider*/ ctx[0] },
    			$$inline: true
    		});

    	const block = {
    		c: function create() {
    			create_component(keyfiledefine.$$.fragment);
    		},
    		m: function mount(target, anchor) {
    			mount_component(keyfiledefine, target, anchor);
    			current = true;
    		},
    		p: function update(ctx, dirty) {
    			const keyfiledefine_changes = {};
    			if (dirty[0] & /*storageProvider*/ 1) keyfiledefine_changes.provider = /*storageProvider*/ ctx[0];
    			keyfiledefine.$set(keyfiledefine_changes);
    		},
    		i: function intro(local) {
    			if (current) return;
    			transition_in(keyfiledefine.$$.fragment, local);
    			current = true;
    		},
    		o: function outro(local) {
    			transition_out(keyfiledefine.$$.fragment, local);
    			current = false;
    		},
    		d: function destroy(detaching) {
    			destroy_component(keyfiledefine, detaching);
    		}
    	};

    	dispatch_dev("SvelteRegisterBlock", {
    		block,
    		id: create_if_block_2$2.name,
    		type: "if",
    		source: "(267:78) ",
    		ctx
    	});

    	return block;
    }

    // (265:4) {#if authMethod === "define" && storageProvider.use_access_keys_allowed}
    function create_if_block_1$3(ctx) {
    	let accesskeysdefine;
    	let current;

    	accesskeysdefine = new AccessKeysDefine({
    			props: { provider: /*storageProvider*/ ctx[0] },
    			$$inline: true
    		});

    	const block = {
    		c: function create() {
    			create_component(accesskeysdefine.$$.fragment);
    		},
    		m: function mount(target, anchor) {
    			mount_component(accesskeysdefine, target, anchor);
    			current = true;
    		},
    		p: function update(ctx, dirty) {
    			const accesskeysdefine_changes = {};
    			if (dirty[0] & /*storageProvider*/ 1) accesskeysdefine_changes.provider = /*storageProvider*/ ctx[0];
    			accesskeysdefine.$set(accesskeysdefine_changes);
    		},
    		i: function intro(local) {
    			if (current) return;
    			transition_in(accesskeysdefine.$$.fragment, local);
    			current = true;
    		},
    		o: function outro(local) {
    			transition_out(accesskeysdefine.$$.fragment, local);
    			current = false;
    		},
    		d: function destroy(detaching) {
    			destroy_component(accesskeysdefine, detaching);
    		}
    	};

    	dispatch_dev("SvelteRegisterBlock", {
    		block,
    		id: create_if_block_1$3.name,
    		type: "if",
    		source: "(265:4) {#if authMethod === \\\"define\\\" && storageProvider.use_access_keys_allowed}",
    		ctx
    	});

    	return block;
    }

    // (264:3) <PanelRow class="body flex-column access-keys">
    function create_default_slot_2$3(ctx) {
    	let current_block_type_index;
    	let if_block;
    	let if_block_anchor;
    	let current;

    	const if_block_creators = [
    		create_if_block_1$3,
    		create_if_block_2$2,
    		create_if_block_3$2,
    		create_if_block_4$2,
    		create_if_block_5$2
    	];

    	const if_blocks = [];

    	function select_block_type_2(ctx, dirty) {
    		if (/*authMethod*/ ctx[1] === "define" && /*storageProvider*/ ctx[0].use_access_keys_allowed) return 0;
    		if (/*authMethod*/ ctx[1] === "define" && /*storageProvider*/ ctx[0].use_key_file_allowed) return 1;
    		if (/*authMethod*/ ctx[1] === "server-role" && /*storageProvider*/ ctx[0].use_server_roles_allowed) return 2;
    		if (/*authMethod*/ ctx[1] === "database" && /*storageProvider*/ ctx[0].use_access_keys_allowed) return 3;
    		if (/*authMethod*/ ctx[1] === "database" && /*storageProvider*/ ctx[0].use_key_file_allowed) return 4;
    		return -1;
    	}

    	if (~(current_block_type_index = select_block_type_2(ctx))) {
    		if_block = if_blocks[current_block_type_index] = if_block_creators[current_block_type_index](ctx);
    	}

    	const block = {
    		c: function create() {
    			if (if_block) if_block.c();
    			if_block_anchor = empty();
    		},
    		m: function mount(target, anchor) {
    			if (~current_block_type_index) {
    				if_blocks[current_block_type_index].m(target, anchor);
    			}

    			insert_dev(target, if_block_anchor, anchor);
    			current = true;
    		},
    		p: function update(ctx, dirty) {
    			let previous_block_index = current_block_type_index;
    			current_block_type_index = select_block_type_2(ctx);

    			if (current_block_type_index === previous_block_index) {
    				if (~current_block_type_index) {
    					if_blocks[current_block_type_index].p(ctx, dirty);
    				}
    			} else {
    				if (if_block) {
    					group_outros();

    					transition_out(if_blocks[previous_block_index], 1, 1, () => {
    						if_blocks[previous_block_index] = null;
    					});

    					check_outros();
    				}

    				if (~current_block_type_index) {
    					if_block = if_blocks[current_block_type_index];

    					if (!if_block) {
    						if_block = if_blocks[current_block_type_index] = if_block_creators[current_block_type_index](ctx);
    						if_block.c();
    					} else {
    						if_block.p(ctx, dirty);
    					}

    					transition_in(if_block, 1);
    					if_block.m(if_block_anchor.parentNode, if_block_anchor);
    				} else {
    					if_block = null;
    				}
    			}
    		},
    		i: function intro(local) {
    			if (current) return;
    			transition_in(if_block);
    			current = true;
    		},
    		o: function outro(local) {
    			transition_out(if_block);
    			current = false;
    		},
    		d: function destroy(detaching) {
    			if (~current_block_type_index) {
    				if_blocks[current_block_type_index].d(detaching);
    			}

    			if (detaching) detach_dev(if_block_anchor);
    		}
    	};

    	dispatch_dev("SvelteRegisterBlock", {
    		block,
    		id: create_default_slot_2$3.name,
    		type: "slot",
    		source: "(264:3) <PanelRow class=\\\"body flex-column access-keys\\\">",
    		ctx
    	});

    	return block;
    }

    // (263:2) <Panel heading={saveCredentialsTitle} multi>
    function create_default_slot_1$3(ctx) {
    	let panelrow;
    	let current;

    	panelrow = new PanelRow({
    			props: {
    				class: "body flex-column access-keys",
    				$$slots: { default: [create_default_slot_2$3] },
    				$$scope: { ctx }
    			},
    			$$inline: true
    		});

    	const block = {
    		c: function create() {
    			create_component(panelrow.$$.fragment);
    		},
    		m: function mount(target, anchor) {
    			mount_component(panelrow, target, anchor);
    			current = true;
    		},
    		p: function update(ctx, dirty) {
    			const panelrow_changes = {};

    			if (dirty[0] & /*storageProvider, authMethod, authDisabled, accessKeyId, secretAccessKey, keyFile*/ 2947 | dirty[1] & /*$$scope*/ 8192) {
    				panelrow_changes.$$scope = { dirty, ctx };
    			}

    			panelrow.$set(panelrow_changes);
    		},
    		i: function intro(local) {
    			if (current) return;
    			transition_in(panelrow.$$.fragment, local);
    			current = true;
    		},
    		o: function outro(local) {
    			transition_out(panelrow.$$.fragment, local);
    			current = false;
    		},
    		d: function destroy(detaching) {
    			destroy_component(panelrow, detaching);
    		}
    	};

    	dispatch_dev("SvelteRegisterBlock", {
    		block,
    		id: create_default_slot_1$3.name,
    		type: "slot",
    		source: "(263:2) <Panel heading={saveCredentialsTitle} multi>",
    		ctx
    	});

    	return block;
    }

    // (203:0) <SubPage name="storage-provider-settings" route="/storage/provider">
    function create_default_slot$9(ctx) {
    	let t0;
    	let panel0;
    	let t1;
    	let panel1;
    	let t2;
    	let t3;
    	let backnextbuttonsrow;
    	let current;
    	let if_block0 = /*changedWithOffloaded*/ ctx[12] && create_if_block_11(ctx);

    	panel0 = new Panel({
    			props: {
    				heading: /*$strings*/ ctx[14].select_storage_provider_title,
    				defined: /*defined*/ ctx[3],
    				multi: true,
    				$$slots: { default: [create_default_slot_10] },
    				$$scope: { ctx }
    			},
    			$$inline: true
    		});

    	panel1 = new Panel({
    			props: {
    				heading: /*$strings*/ ctx[14].select_auth_method_title,
    				defined: /*authDefined*/ ctx[2],
    				multi: true,
    				$$slots: { default: [create_default_slot_3$3] },
    				$$scope: { ctx }
    			},
    			$$inline: true
    		});

    	let if_block1 = !/*authDefined*/ ctx[2] && create_if_block$6(ctx);

    	backnextbuttonsrow = new BackNextButtonsRow({
    			props: {
    				nextDisabled: /*$needs_refresh*/ ctx[5] || /*$settingsLocked*/ ctx[4],
    				nextText: /*$strings*/ ctx[14].save_and_continue
    			},
    			$$inline: true
    		});

    	backnextbuttonsrow.$on("next", /*handleNext*/ ctx[17]);

    	const block = {
    		c: function create() {
    			if (if_block0) if_block0.c();
    			t0 = space();
    			create_component(panel0.$$.fragment);
    			t1 = space();
    			create_component(panel1.$$.fragment);
    			t2 = space();
    			if (if_block1) if_block1.c();
    			t3 = space();
    			create_component(backnextbuttonsrow.$$.fragment);
    		},
    		m: function mount(target, anchor) {
    			if (if_block0) if_block0.m(target, anchor);
    			insert_dev(target, t0, anchor);
    			mount_component(panel0, target, anchor);
    			insert_dev(target, t1, anchor);
    			mount_component(panel1, target, anchor);
    			insert_dev(target, t2, anchor);
    			if (if_block1) if_block1.m(target, anchor);
    			insert_dev(target, t3, anchor);
    			mount_component(backnextbuttonsrow, target, anchor);
    			current = true;
    		},
    		p: function update(ctx, dirty) {
    			if (/*changedWithOffloaded*/ ctx[12]) {
    				if (if_block0) {
    					if_block0.p(ctx, dirty);

    					if (dirty[0] & /*changedWithOffloaded*/ 4096) {
    						transition_in(if_block0, 1);
    					}
    				} else {
    					if_block0 = create_if_block_11(ctx);
    					if_block0.c();
    					transition_in(if_block0, 1);
    					if_block0.m(t0.parentNode, t0);
    				}
    			} else if (if_block0) {
    				group_outros();

    				transition_out(if_block0, 1, 1, () => {
    					if_block0 = null;
    				});

    				check_outros();
    			}

    			const panel0_changes = {};
    			if (dirty[0] & /*$strings*/ 16384) panel0_changes.heading = /*$strings*/ ctx[14].select_storage_provider_title;
    			if (dirty[0] & /*defined*/ 8) panel0_changes.defined = /*defined*/ ctx[3];

    			if (dirty[0] & /*storageProvider, $storage_providers, disabled*/ 40961 | dirty[1] & /*$$scope*/ 8192) {
    				panel0_changes.$$scope = { dirty, ctx };
    			}

    			panel0.$set(panel0_changes);
    			const panel1_changes = {};
    			if (dirty[0] & /*$strings*/ 16384) panel1_changes.heading = /*$strings*/ ctx[14].select_auth_method_title;
    			if (dirty[0] & /*authDefined*/ 4) panel1_changes.defined = /*authDefined*/ ctx[2];

    			if (dirty[0] & /*authDisabled, authMethod, $strings, storageProvider*/ 18435 | dirty[1] & /*$$scope*/ 8192) {
    				panel1_changes.$$scope = { dirty, ctx };
    			}

    			panel1.$set(panel1_changes);

    			if (!/*authDefined*/ ctx[2]) {
    				if (if_block1) {
    					if_block1.p(ctx, dirty);

    					if (dirty[0] & /*authDefined*/ 4) {
    						transition_in(if_block1, 1);
    					}
    				} else {
    					if_block1 = create_if_block$6(ctx);
    					if_block1.c();
    					transition_in(if_block1, 1);
    					if_block1.m(t3.parentNode, t3);
    				}
    			} else if (if_block1) {
    				group_outros();

    				transition_out(if_block1, 1, 1, () => {
    					if_block1 = null;
    				});

    				check_outros();
    			}

    			const backnextbuttonsrow_changes = {};
    			if (dirty[0] & /*$needs_refresh, $settingsLocked*/ 48) backnextbuttonsrow_changes.nextDisabled = /*$needs_refresh*/ ctx[5] || /*$settingsLocked*/ ctx[4];
    			if (dirty[0] & /*$strings*/ 16384) backnextbuttonsrow_changes.nextText = /*$strings*/ ctx[14].save_and_continue;
    			backnextbuttonsrow.$set(backnextbuttonsrow_changes);
    		},
    		i: function intro(local) {
    			if (current) return;
    			transition_in(if_block0);
    			transition_in(panel0.$$.fragment, local);
    			transition_in(panel1.$$.fragment, local);
    			transition_in(if_block1);
    			transition_in(backnextbuttonsrow.$$.fragment, local);
    			current = true;
    		},
    		o: function outro(local) {
    			transition_out(if_block0);
    			transition_out(panel0.$$.fragment, local);
    			transition_out(panel1.$$.fragment, local);
    			transition_out(if_block1);
    			transition_out(backnextbuttonsrow.$$.fragment, local);
    			current = false;
    		},
    		d: function destroy(detaching) {
    			if (if_block0) if_block0.d(detaching);
    			if (detaching) detach_dev(t0);
    			destroy_component(panel0, detaching);
    			if (detaching) detach_dev(t1);
    			destroy_component(panel1, detaching);
    			if (detaching) detach_dev(t2);
    			if (if_block1) if_block1.d(detaching);
    			if (detaching) detach_dev(t3);
    			destroy_component(backnextbuttonsrow, detaching);
    		}
    	};

    	dispatch_dev("SvelteRegisterBlock", {
    		block,
    		id: create_default_slot$9.name,
    		type: "slot",
    		source: "(203:0) <SubPage name=\\\"storage-provider-settings\\\" route=\\\"/storage/provider\\\">",
    		ctx
    	});

    	return block;
    }

    function create_fragment$j(ctx) {
    	let subpage;
    	let current;

    	subpage = new SubPage({
    			props: {
    				name: "storage-provider-settings",
    				route: "/storage/provider",
    				$$slots: { default: [create_default_slot$9] },
    				$$scope: { ctx }
    			},
    			$$inline: true
    		});

    	const block = {
    		c: function create() {
    			create_component(subpage.$$.fragment);
    		},
    		l: function claim(nodes) {
    			throw new Error("options.hydrate only works if the component was compiled with the `hydratable: true` option");
    		},
    		m: function mount(target, anchor) {
    			mount_component(subpage, target, anchor);
    			current = true;
    		},
    		p: function update(ctx, dirty) {
    			const subpage_changes = {};

    			if (dirty[0] & /*$needs_refresh, $settingsLocked, $strings, saveCredentialsTitle, storageProvider, authMethod, authDisabled, accessKeyId, secretAccessKey, keyFile, authDefined, defined, $storage_providers, disabled, changedWithOffloaded*/ 65471 | dirty[1] & /*$$scope*/ 8192) {
    				subpage_changes.$$scope = { dirty, ctx };
    			}

    			subpage.$set(subpage_changes);
    		},
    		i: function intro(local) {
    			if (current) return;
    			transition_in(subpage.$$.fragment, local);
    			current = true;
    		},
    		o: function outro(local) {
    			transition_out(subpage.$$.fragment, local);
    			current = false;
    		},
    		d: function destroy(detaching) {
    			destroy_component(subpage, detaching);
    		}
    	};

    	dispatch_dev("SvelteRegisterBlock", {
    		block,
    		id: create_fragment$j.name,
    		type: "component",
    		source: "",
    		ctx
    	});

    	return block;
    }

    function instance$j($$self, $$props, $$invalidate) {
    	let defined;
    	let disabled;
    	let changedWithOffloaded;
    	let authDefined;
    	let authDisabled;
    	let saveCredentialsTitle;
    	let $settings;
    	let $strings;

    	let $settingsLocked,
    		$$unsubscribe_settingsLocked = noop,
    		$$subscribe_settingsLocked = () => ($$unsubscribe_settingsLocked(), $$unsubscribe_settingsLocked = subscribe(settingsLocked, $$value => $$invalidate(4, $settingsLocked = $$value)), settingsLocked);

    	let $needs_refresh;
    	let $counts;
    	let $defined_settings;
    	let $storage_provider;
    	let $current_settings;
    	let $storage_providers;
    	validate_store(settings, 'settings');
    	component_subscribe($$self, settings, $$value => $$invalidate(33, $settings = $$value));
    	validate_store(strings, 'strings');
    	component_subscribe($$self, strings, $$value => $$invalidate(14, $strings = $$value));
    	validate_store(needs_refresh, 'needs_refresh');
    	component_subscribe($$self, needs_refresh, $$value => $$invalidate(5, $needs_refresh = $$value));
    	validate_store(counts, 'counts');
    	component_subscribe($$self, counts, $$value => $$invalidate(21, $counts = $$value));
    	validate_store(defined_settings, 'defined_settings');
    	component_subscribe($$self, defined_settings, $$value => $$invalidate(22, $defined_settings = $$value));
    	validate_store(storage_provider, 'storage_provider');
    	component_subscribe($$self, storage_provider, $$value => $$invalidate(34, $storage_provider = $$value));
    	validate_store(current_settings, 'current_settings');
    	component_subscribe($$self, current_settings, $$value => $$invalidate(23, $current_settings = $$value));
    	validate_store(storage_providers, 'storage_providers');
    	component_subscribe($$self, storage_providers, $$value => $$invalidate(15, $storage_providers = $$value));
    	$$self.$$.on_destroy.push(() => $$unsubscribe_settingsLocked());
    	let { $$slots: slots = {}, $$scope } = $$props;
    	validate_slots('StorageProviderSubPage', slots, []);
    	let { params = {} } = $$props;
    	const _params = params; // Stops compiler warning about unused params export;
    	const dispatch = createEventDispatcher();

    	// Parent page may want to be locked.
    	let settingsLocked = writable(false);

    	validate_store(settingsLocked, 'settingsLocked');
    	$$subscribe_settingsLocked();

    	if (hasContext("settingsLocked")) {
    		$$subscribe_settingsLocked(settingsLocked = getContext("settingsLocked"));
    	}

    	// Need to be careful about throwing unneeded warnings.
    	let initialSettings = $current_settings;

    	if (hasContext("initialSettings")) {
    		initialSettings = getContext("initialSettings");
    	}

    	// As this page does not directly alter the settings store until done,
    	// we need to keep track of any changes made elsewhere and prompt
    	// the user to refresh the page.
    	let saving = false;

    	const previousSettings = { ...$current_settings };
    	const previousDefines = { ...$defined_settings };

    	/*
     * 1. Select Storage Provider
     */
    	let storageProvider = { ...$storage_provider };

    	/**
     * Handles picking different storage provider.
     *
     * @param {Object} provider
     */
    	function handleChooseProvider(provider) {
    		if (disabled) {
    			return;
    		}

    		$$invalidate(0, storageProvider = provider);

    		// Now make sure authMethod is valid for chosen storage provider.
    		$$invalidate(1, authMethod = getAuthMethod(storageProvider, authMethod));
    	}

    	/*
     * 2. Select Authentication method
     */
    	let accessKeyId = $settings["access-key-id"];

    	let secretAccessKey = $settings["secret-access-key"];

    	let keyFile = $settings["key-file"]
    	? JSON.stringify($settings["key-file"])
    	: "";

    	/**
     * For the given current storage provider, determine the authentication method or fallback to currently selected.
     * It's possible that the storage provider can be freely changed but the
     * authentication method is defined (fixed) differently for each, or freely changeable too.
     * The order of evaluation in this function is important and mirrors the server side evaluation order.
     *
     * @param {provider} provider
     * @param {string} current auth method, one of "define", "server-role" or "database" if set.
     *
     * @return {string}
     */
    	function getAuthMethod(provider, current = "") {
    		if (provider.use_access_keys_allowed && provider.used_access_keys_constants.length) {
    			return "define";
    		}

    		if (provider.use_key_file_allowed && provider.used_key_file_path_constants.length) {
    			return "define";
    		}

    		if (provider.use_server_roles_allowed && provider.used_server_roles_constants.length) {
    			return "server-role";
    		}

    		if (current === "server-role" && !provider.use_server_roles_allowed) {
    			return "define";
    		}

    		if (current.length === 0) {
    			if (provider.use_access_keys_allowed && (accessKeyId || secretAccessKey)) {
    				return "database";
    			}

    			if (provider.use_key_file_allowed && keyFile) {
    				return "database";
    			}

    			if (provider.use_server_roles_allowed && $settings["use-server-roles"]) {
    				return "server-role";
    			}

    			// Default to most secure option.
    			return "define";
    		}

    		return current;
    	}

    	let authMethod = getAuthMethod(storageProvider);

    	/*
     * 3. Save Authentication Credentials
     */
    	/**
     * Returns a title string to be used for the credentials panel as appropriate for the auth method.
     *
     * @param {string} method
     * @return {*}
     */
    	function getCredentialsTitle(method) {
    		return $strings.auth_method_title[method];
    	}

    	/*
     * Do Something!
     */
    	/**
     * Handles a Next button click.
     *
     * @return {Promise<void>}
     */
    	async function handleNext() {
    		$$invalidate(20, saving = true);
    		state.pausePeriodicFetch();
    		set_store_value(settings, $settings.provider = storageProvider.provider_key_name, $settings);
    		set_store_value(settings, $settings["access-key-id"] = accessKeyId, $settings);
    		set_store_value(settings, $settings["secret-access-key"] = secretAccessKey, $settings);
    		set_store_value(settings, $settings["key-file"] = keyFile, $settings);
    		const result = await settings.save();

    		// If something went wrong, don't move onto next step.
    		if (!result.hasOwnProperty("saved") || !result.saved) {
    			settings.reset();
    			$$invalidate(20, saving = false);
    			state.resumePeriodicFetch();
    			scrollNotificationsIntoView();
    			return;
    		}

    		state.resumePeriodicFetch();
    		dispatch("routeEvent", { event: "settings.save", data: result });
    	}

    	const writable_props = ['params'];

    	Object_1$2.keys($$props).forEach(key => {
    		if (!~writable_props.indexOf(key) && key.slice(0, 2) !== '$$' && key !== 'slot') console.warn(`<StorageProviderSubPage> was created with unknown prop '${key}'`);
    	});

    	const click_handler = provider => handleChooseProvider(provider);

    	function radiobutton_selected_binding(value) {
    		authMethod = value;
    		$$invalidate(1, authMethod);
    	}

    	function radiobutton_selected_binding_1(value) {
    		authMethod = value;
    		$$invalidate(1, authMethod);
    	}

    	function radiobutton_selected_binding_2(value) {
    		authMethod = value;
    		$$invalidate(1, authMethod);
    	}

    	function radiobutton_selected_binding_3(value) {
    		authMethod = value;
    		$$invalidate(1, authMethod);
    	}

    	function radiobutton_selected_binding_4(value) {
    		authMethod = value;
    		$$invalidate(1, authMethod);
    	}

    	function accesskeysentry_accessKeyId_binding(value) {
    		accessKeyId = value;
    		$$invalidate(7, accessKeyId);
    	}

    	function accesskeysentry_secretAccessKey_binding(value) {
    		secretAccessKey = value;
    		$$invalidate(8, secretAccessKey);
    	}

    	function keyfileentry_value_binding(value) {
    		keyFile = value;
    		$$invalidate(9, keyFile);
    	}

    	$$self.$$set = $$props => {
    		if ('params' in $$props) $$invalidate(18, params = $$props.params);
    	};

    	$$self.$capture_state = () => ({
    		createEventDispatcher,
    		getContext,
    		hasContext,
    		writable,
    		replace,
    		settings,
    		defined_settings,
    		strings,
    		storage_providers,
    		storage_provider,
    		counts,
    		current_settings,
    		needs_refresh,
    		state,
    		scrollNotificationsIntoView,
    		needsRefresh,
    		SubPage,
    		Panel,
    		PanelRow,
    		TabButton,
    		RadioButton,
    		AccessKeysDefine,
    		BackNextButtonsRow,
    		KeyFileDefine,
    		UseServerRolesDefine,
    		AccessKeysEntry,
    		KeyFileEntry,
    		Notification,
    		params,
    		_params,
    		dispatch,
    		settingsLocked,
    		initialSettings,
    		saving,
    		previousSettings,
    		previousDefines,
    		storageProvider,
    		handleChooseProvider,
    		accessKeyId,
    		secretAccessKey,
    		keyFile,
    		getAuthMethod,
    		authMethod,
    		getCredentialsTitle,
    		handleNext,
    		saveCredentialsTitle,
    		authDefined,
    		authDisabled,
    		changedWithOffloaded,
    		disabled,
    		defined,
    		$settings,
    		$strings,
    		$settingsLocked,
    		$needs_refresh,
    		$counts,
    		$defined_settings,
    		$storage_provider,
    		$current_settings,
    		$storage_providers
    	});

    	$$self.$inject_state = $$props => {
    		if ('params' in $$props) $$invalidate(18, params = $$props.params);
    		if ('settingsLocked' in $$props) $$subscribe_settingsLocked($$invalidate(6, settingsLocked = $$props.settingsLocked));
    		if ('initialSettings' in $$props) $$invalidate(19, initialSettings = $$props.initialSettings);
    		if ('saving' in $$props) $$invalidate(20, saving = $$props.saving);
    		if ('storageProvider' in $$props) $$invalidate(0, storageProvider = $$props.storageProvider);
    		if ('accessKeyId' in $$props) $$invalidate(7, accessKeyId = $$props.accessKeyId);
    		if ('secretAccessKey' in $$props) $$invalidate(8, secretAccessKey = $$props.secretAccessKey);
    		if ('keyFile' in $$props) $$invalidate(9, keyFile = $$props.keyFile);
    		if ('authMethod' in $$props) $$invalidate(1, authMethod = $$props.authMethod);
    		if ('saveCredentialsTitle' in $$props) $$invalidate(10, saveCredentialsTitle = $$props.saveCredentialsTitle);
    		if ('authDefined' in $$props) $$invalidate(2, authDefined = $$props.authDefined);
    		if ('authDisabled' in $$props) $$invalidate(11, authDisabled = $$props.authDisabled);
    		if ('changedWithOffloaded' in $$props) $$invalidate(12, changedWithOffloaded = $$props.changedWithOffloaded);
    		if ('disabled' in $$props) $$invalidate(13, disabled = $$props.disabled);
    		if ('defined' in $$props) $$invalidate(3, defined = $$props.defined);
    	};

    	if ($$props && "$$inject" in $$props) {
    		$$self.$inject_state($$props.$$inject);
    	}

    	$$self.$$.update = () => {
    		if ($$self.$$.dirty[0] & /*$needs_refresh, saving, $current_settings, $defined_settings*/ 13631520) {
    			{
    				set_store_value(needs_refresh, $needs_refresh = $needs_refresh || needsRefresh(saving, previousSettings, $current_settings, previousDefines, $defined_settings), $needs_refresh);
    			}
    		}

    		if ($$self.$$.dirty[0] & /*$defined_settings*/ 4194304) {
    			$$invalidate(3, defined = $defined_settings.includes("provider"));
    		}

    		if ($$self.$$.dirty[0] & /*defined, $needs_refresh, $settingsLocked*/ 56) {
    			$$invalidate(13, disabled = defined || $needs_refresh || $settingsLocked);
    		}

    		if ($$self.$$.dirty[0] & /*initialSettings, storageProvider, $counts*/ 2621441) {
    			$$invalidate(12, changedWithOffloaded = initialSettings.provider !== storageProvider.provider_key_name && $counts.offloaded > 0);
    		}

    		if ($$self.$$.dirty[0] & /*storageProvider*/ 1) {
    			// If auth method is not allowed to be database, then either define or server-role is being forced, likely by a define.
    			$$invalidate(2, authDefined = "database" !== getAuthMethod(storageProvider, "database"));
    		}

    		if ($$self.$$.dirty[0] & /*authDefined, $needs_refresh, $settingsLocked*/ 52) {
    			$$invalidate(11, authDisabled = authDefined || $needs_refresh || $settingsLocked);
    		}

    		if ($$self.$$.dirty[0] & /*authMethod*/ 2) {
    			$$invalidate(10, saveCredentialsTitle = getCredentialsTitle(authMethod));
    		}
    	};

    	return [
    		storageProvider,
    		authMethod,
    		authDefined,
    		defined,
    		$settingsLocked,
    		$needs_refresh,
    		settingsLocked,
    		accessKeyId,
    		secretAccessKey,
    		keyFile,
    		saveCredentialsTitle,
    		authDisabled,
    		changedWithOffloaded,
    		disabled,
    		$strings,
    		$storage_providers,
    		handleChooseProvider,
    		handleNext,
    		params,
    		initialSettings,
    		saving,
    		$counts,
    		$defined_settings,
    		$current_settings,
    		click_handler,
    		radiobutton_selected_binding,
    		radiobutton_selected_binding_1,
    		radiobutton_selected_binding_2,
    		radiobutton_selected_binding_3,
    		radiobutton_selected_binding_4,
    		accesskeysentry_accessKeyId_binding,
    		accesskeysentry_secretAccessKey_binding,
    		keyfileentry_value_binding
    	];
    }

    class StorageProviderSubPage extends SvelteComponentDev {
    	constructor(options) {
    		super(options);
    		init(this, options, instance$j, create_fragment$j, safe_not_equal, { params: 18 }, null, [-1, -1]);

    		dispatch_dev("SvelteRegisterComponent", {
    			component: this,
    			tagName: "StorageProviderSubPage",
    			options,
    			id: create_fragment$j.name
    		});
    	}

    	get params() {
    		throw new Error("<StorageProviderSubPage>: Props cannot be read directly from the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
    	}

    	set params(value) {
    		throw new Error("<StorageProviderSubPage>: Props cannot be set directly on the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
    	}
    }

    /**
     * A simple action to scroll the element into view if active.
     *
     * @param {Object} node
     * @param {boolean} active
     */
    function scrollIntoView( node, active ) {
    	if ( active ) {
    		node.scrollIntoView( { behavior: "smooth", block: "center", inline: "nearest" } );
    	}
    }

    /* src/amazon-s3-and-cloudfront/ui/components/Loading.svelte generated by Svelte v3.49.0 */
    const file$g = "src/amazon-s3-and-cloudfront/ui/components/Loading.svelte";

    function create_fragment$i(ctx) {
    	let p;
    	let t_value = /*$strings*/ ctx[0].loading + "";
    	let t;

    	const block = {
    		c: function create() {
    			p = element("p");
    			t = text(t_value);
    			add_location(p, file$g, 4, 0, 59);
    		},
    		l: function claim(nodes) {
    			throw new Error("options.hydrate only works if the component was compiled with the `hydratable: true` option");
    		},
    		m: function mount(target, anchor) {
    			insert_dev(target, p, anchor);
    			append_dev(p, t);
    		},
    		p: function update(ctx, [dirty]) {
    			if (dirty & /*$strings*/ 1 && t_value !== (t_value = /*$strings*/ ctx[0].loading + "")) set_data_dev(t, t_value);
    		},
    		i: noop,
    		o: noop,
    		d: function destroy(detaching) {
    			if (detaching) detach_dev(p);
    		}
    	};

    	dispatch_dev("SvelteRegisterBlock", {
    		block,
    		id: create_fragment$i.name,
    		type: "component",
    		source: "",
    		ctx
    	});

    	return block;
    }

    function instance$i($$self, $$props, $$invalidate) {
    	let $strings;
    	validate_store(strings, 'strings');
    	component_subscribe($$self, strings, $$value => $$invalidate(0, $strings = $$value));
    	let { $$slots: slots = {}, $$scope } = $$props;
    	validate_slots('Loading', slots, []);
    	const writable_props = [];

    	Object.keys($$props).forEach(key => {
    		if (!~writable_props.indexOf(key) && key.slice(0, 2) !== '$$' && key !== 'slot') console.warn(`<Loading> was created with unknown prop '${key}'`);
    	});

    	$$self.$capture_state = () => ({ strings, $strings });
    	return [$strings];
    }

    class Loading extends SvelteComponentDev {
    	constructor(options) {
    		super(options);
    		init(this, options, instance$i, create_fragment$i, safe_not_equal, {});

    		dispatch_dev("SvelteRegisterComponent", {
    			component: this,
    			tagName: "Loading",
    			options,
    			id: create_fragment$i.name
    		});
    	}
    }

    /* src/amazon-s3-and-cloudfront/ui/components/BucketSettingsSubPage.svelte generated by Svelte v3.49.0 */

    const { Object: Object_1$1 } = globals;
    const file$f = "src/amazon-s3-and-cloudfront/ui/components/BucketSettingsSubPage.svelte";

    function get_each_context$3(ctx, list, i) {
    	const child_ctx = ctx.slice();
    	child_ctx[39] = list[i][0];
    	child_ctx[40] = list[i][1];
    	child_ctx[42] = i;
    	return child_ctx;
    }

    function get_each_context_1(ctx, list, i) {
    	const child_ctx = ctx.slice();
    	child_ctx[44] = list[i];
    	return child_ctx;
    }

    function get_each_context_2(ctx, list, i) {
    	const child_ctx = ctx.slice();
    	child_ctx[39] = list[i][0];
    	child_ctx[40] = list[i][1];
    	child_ctx[42] = i;
    	return child_ctx;
    }

    function get_each_context_3(ctx, list, i) {
    	const child_ctx = ctx.slice();
    	child_ctx[39] = list[i][0];
    	child_ctx[40] = list[i][1];
    	child_ctx[42] = i;
    	return child_ctx;
    }

    // (260:2) <PanelRow class="body flex-row tab-buttons">
    function create_default_slot_8$1(ctx) {
    	let tabbutton0;
    	let t;
    	let tabbutton1;
    	let current;

    	tabbutton0 = new TabButton({
    			props: {
    				active: /*bucketSource*/ ctx[0] === "existing",
    				disabled: /*disabled*/ ctx[11],
    				text: /*$strings*/ ctx[14].use_existing_bucket
    			},
    			$$inline: true
    		});

    	tabbutton0.$on("click", /*handleExisting*/ ctx[16]);

    	tabbutton1 = new TabButton({
    			props: {
    				active: /*bucketSource*/ ctx[0] === "new",
    				disabled: /*disabled*/ ctx[11],
    				text: /*$strings*/ ctx[14].create_new_bucket
    			},
    			$$inline: true
    		});

    	tabbutton1.$on("click", /*handleNew*/ ctx[17]);

    	const block = {
    		c: function create() {
    			create_component(tabbutton0.$$.fragment);
    			t = space();
    			create_component(tabbutton1.$$.fragment);
    		},
    		m: function mount(target, anchor) {
    			mount_component(tabbutton0, target, anchor);
    			insert_dev(target, t, anchor);
    			mount_component(tabbutton1, target, anchor);
    			current = true;
    		},
    		p: function update(ctx, dirty) {
    			const tabbutton0_changes = {};
    			if (dirty[0] & /*bucketSource*/ 1) tabbutton0_changes.active = /*bucketSource*/ ctx[0] === "existing";
    			if (dirty[0] & /*disabled*/ 2048) tabbutton0_changes.disabled = /*disabled*/ ctx[11];
    			if (dirty[0] & /*$strings*/ 16384) tabbutton0_changes.text = /*$strings*/ ctx[14].use_existing_bucket;
    			tabbutton0.$set(tabbutton0_changes);
    			const tabbutton1_changes = {};
    			if (dirty[0] & /*bucketSource*/ 1) tabbutton1_changes.active = /*bucketSource*/ ctx[0] === "new";
    			if (dirty[0] & /*disabled*/ 2048) tabbutton1_changes.disabled = /*disabled*/ ctx[11];
    			if (dirty[0] & /*$strings*/ 16384) tabbutton1_changes.text = /*$strings*/ ctx[14].create_new_bucket;
    			tabbutton1.$set(tabbutton1_changes);
    		},
    		i: function intro(local) {
    			if (current) return;
    			transition_in(tabbutton0.$$.fragment, local);
    			transition_in(tabbutton1.$$.fragment, local);
    			current = true;
    		},
    		o: function outro(local) {
    			transition_out(tabbutton0.$$.fragment, local);
    			transition_out(tabbutton1.$$.fragment, local);
    			current = false;
    		},
    		d: function destroy(detaching) {
    			destroy_component(tabbutton0, detaching);
    			if (detaching) detach_dev(t);
    			destroy_component(tabbutton1, detaching);
    		}
    	};

    	dispatch_dev("SvelteRegisterBlock", {
    		block,
    		id: create_default_slot_8$1.name,
    		type: "slot",
    		source: "(260:2) <PanelRow class=\\\"body flex-row tab-buttons\\\">",
    		ctx
    	});

    	return block;
    }

    // (259:1) <Panel heading={$strings.bucket_source_title} multi {defined}>
    function create_default_slot_7$1(ctx) {
    	let panelrow;
    	let current;

    	panelrow = new PanelRow({
    			props: {
    				class: "body flex-row tab-buttons",
    				$$slots: { default: [create_default_slot_8$1] },
    				$$scope: { ctx }
    			},
    			$$inline: true
    		});

    	const block = {
    		c: function create() {
    			create_component(panelrow.$$.fragment);
    		},
    		m: function mount(target, anchor) {
    			mount_component(panelrow, target, anchor);
    			current = true;
    		},
    		p: function update(ctx, dirty) {
    			const panelrow_changes = {};

    			if (dirty[0] & /*bucketSource, disabled, $strings*/ 18433 | dirty[1] & /*$$scope*/ 262144) {
    				panelrow_changes.$$scope = { dirty, ctx };
    			}

    			panelrow.$set(panelrow_changes);
    		},
    		i: function intro(local) {
    			if (current) return;
    			transition_in(panelrow.$$.fragment, local);
    			current = true;
    		},
    		o: function outro(local) {
    			transition_out(panelrow.$$.fragment, local);
    			current = false;
    		},
    		d: function destroy(detaching) {
    			destroy_component(panelrow, detaching);
    		}
    	};

    	dispatch_dev("SvelteRegisterBlock", {
    		block,
    		id: create_default_slot_7$1.name,
    		type: "slot",
    		source: "(259:1) <Panel heading={$strings.bucket_source_title} multi {defined}>",
    		ctx
    	});

    	return block;
    }

    // (276:1) {#if bucketSource === "existing"}
    function create_if_block_2$1(ctx) {
    	let panel;
    	let current;

    	panel = new Panel({
    			props: {
    				heading: /*$strings*/ ctx[14].existing_bucket_title,
    				storageProvider: /*$storage_provider*/ ctx[13],
    				multi: true,
    				defined: /*defined*/ ctx[4],
    				$$slots: { default: [create_default_slot_3$2] },
    				$$scope: { ctx }
    			},
    			$$inline: true
    		});

    	const block = {
    		c: function create() {
    			create_component(panel.$$.fragment);
    		},
    		m: function mount(target, anchor) {
    			mount_component(panel, target, anchor);
    			current = true;
    		},
    		p: function update(ctx, dirty) {
    			const panel_changes = {};
    			if (dirty[0] & /*$strings*/ 16384) panel_changes.heading = /*$strings*/ ctx[14].existing_bucket_title;
    			if (dirty[0] & /*$storage_provider*/ 8192) panel_changes.storageProvider = /*$storage_provider*/ ctx[13];
    			if (dirty[0] & /*defined*/ 16) panel_changes.defined = /*defined*/ ctx[4];

    			if (dirty[0] & /*invalidBucketNameMessage, newRegion, newBucket, $urls, $strings, newRegionDisabled, $storage_provider, newRegionDefined, enterOrSelectExisting, disabled*/ 64782 | dirty[1] & /*$$scope*/ 262144) {
    				panel_changes.$$scope = { dirty, ctx };
    			}

    			panel.$set(panel_changes);
    		},
    		i: function intro(local) {
    			if (current) return;
    			transition_in(panel.$$.fragment, local);
    			current = true;
    		},
    		o: function outro(local) {
    			transition_out(panel.$$.fragment, local);
    			current = false;
    		},
    		d: function destroy(detaching) {
    			destroy_component(panel, detaching);
    		}
    	};

    	dispatch_dev("SvelteRegisterBlock", {
    		block,
    		id: create_if_block_2$1.name,
    		type: "if",
    		source: "(276:1) {#if bucketSource === \\\"existing\\\"}",
    		ctx
    	});

    	return block;
    }

    // (280:5) <RadioButton bind:selected={enterOrSelectExisting} value="enter" list {disabled}>
    function create_default_slot_6$1(ctx) {
    	let t_value = /*$strings*/ ctx[14].enter_bucket + "";
    	let t;

    	const block = {
    		c: function create() {
    			t = text(t_value);
    		},
    		m: function mount(target, anchor) {
    			insert_dev(target, t, anchor);
    		},
    		p: function update(ctx, dirty) {
    			if (dirty[0] & /*$strings*/ 16384 && t_value !== (t_value = /*$strings*/ ctx[14].enter_bucket + "")) set_data_dev(t, t_value);
    		},
    		d: function destroy(detaching) {
    			if (detaching) detach_dev(t);
    		}
    	};

    	dispatch_dev("SvelteRegisterBlock", {
    		block,
    		id: create_default_slot_6$1.name,
    		type: "slot",
    		source: "(280:5) <RadioButton bind:selected={enterOrSelectExisting} value=\\\"enter\\\" list {disabled}>",
    		ctx
    	});

    	return block;
    }

    // (281:5) <RadioButton bind:selected={enterOrSelectExisting} value="select" list {disabled}>
    function create_default_slot_5$1(ctx) {
    	let t_value = /*$strings*/ ctx[14].select_bucket + "";
    	let t;

    	const block = {
    		c: function create() {
    			t = text(t_value);
    		},
    		m: function mount(target, anchor) {
    			insert_dev(target, t, anchor);
    		},
    		p: function update(ctx, dirty) {
    			if (dirty[0] & /*$strings*/ 16384 && t_value !== (t_value = /*$strings*/ ctx[14].select_bucket + "")) set_data_dev(t, t_value);
    		},
    		d: function destroy(detaching) {
    			if (detaching) detach_dev(t);
    		}
    	};

    	dispatch_dev("SvelteRegisterBlock", {
    		block,
    		id: create_default_slot_5$1.name,
    		type: "slot",
    		source: "(281:5) <RadioButton bind:selected={enterOrSelectExisting} value=\\\"select\\\" list {disabled}>",
    		ctx
    	});

    	return block;
    }

    // (284:4) {#if enterOrSelectExisting === "enter"}
    function create_if_block_8$1(ctx) {
    	let div1;
    	let div0;
    	let label;
    	let t0_value = /*$strings*/ ctx[14].bucket_name + "";
    	let t0;
    	let t1;
    	let input;
    	let input_placeholder_value;
    	let t2;
    	let current;
    	let mounted;
    	let dispose;
    	let if_block = /*$storage_provider*/ ctx[13].region_required && create_if_block_9$1(ctx);

    	const block = {
    		c: function create() {
    			div1 = element("div");
    			div0 = element("div");
    			label = element("label");
    			t0 = text(t0_value);
    			t1 = space();
    			input = element("input");
    			t2 = space();
    			if (if_block) if_block.c();
    			attr_dev(label, "class", "input-label");
    			attr_dev(label, "for", "bucket-name");
    			add_location(label, file$f, 286, 7, 7863);
    			attr_dev(input, "type", "text");
    			attr_dev(input, "id", "bucket-name");
    			attr_dev(input, "class", "bucket-name");
    			attr_dev(input, "name", "bucket");
    			attr_dev(input, "minlength", "3");
    			attr_dev(input, "placeholder", input_placeholder_value = /*$strings*/ ctx[14].enter_bucket_name_placeholder);
    			input.disabled = /*disabled*/ ctx[11];
    			toggle_class(input, "disabled", /*disabled*/ ctx[11]);
    			add_location(input, file$f, 287, 7, 7946);
    			attr_dev(div0, "class", "new-bucket-details flex-column");
    			add_location(div0, file$f, 285, 6, 7811);
    			attr_dev(div1, "class", "flex-row align-center row");
    			add_location(div1, file$f, 284, 5, 7765);
    		},
    		m: function mount(target, anchor) {
    			insert_dev(target, div1, anchor);
    			append_dev(div1, div0);
    			append_dev(div0, label);
    			append_dev(label, t0);
    			append_dev(div0, t1);
    			append_dev(div0, input);
    			set_input_value(input, /*newBucket*/ ctx[2]);
    			append_dev(div1, t2);
    			if (if_block) if_block.m(div1, null);
    			current = true;

    			if (!mounted) {
    				dispose = listen_dev(input, "input", /*input_input_handler*/ ctx[25]);
    				mounted = true;
    			}
    		},
    		p: function update(ctx, dirty) {
    			if ((!current || dirty[0] & /*$strings*/ 16384) && t0_value !== (t0_value = /*$strings*/ ctx[14].bucket_name + "")) set_data_dev(t0, t0_value);

    			if (!current || dirty[0] & /*$strings*/ 16384 && input_placeholder_value !== (input_placeholder_value = /*$strings*/ ctx[14].enter_bucket_name_placeholder)) {
    				attr_dev(input, "placeholder", input_placeholder_value);
    			}

    			if (!current || dirty[0] & /*disabled*/ 2048) {
    				prop_dev(input, "disabled", /*disabled*/ ctx[11]);
    			}

    			if (dirty[0] & /*newBucket*/ 4 && input.value !== /*newBucket*/ ctx[2]) {
    				set_input_value(input, /*newBucket*/ ctx[2]);
    			}

    			if (dirty[0] & /*disabled*/ 2048) {
    				toggle_class(input, "disabled", /*disabled*/ ctx[11]);
    			}

    			if (/*$storage_provider*/ ctx[13].region_required) {
    				if (if_block) {
    					if_block.p(ctx, dirty);

    					if (dirty[0] & /*$storage_provider*/ 8192) {
    						transition_in(if_block, 1);
    					}
    				} else {
    					if_block = create_if_block_9$1(ctx);
    					if_block.c();
    					transition_in(if_block, 1);
    					if_block.m(div1, null);
    				}
    			} else if (if_block) {
    				group_outros();

    				transition_out(if_block, 1, 1, () => {
    					if_block = null;
    				});

    				check_outros();
    			}
    		},
    		i: function intro(local) {
    			if (current) return;
    			transition_in(if_block);
    			current = true;
    		},
    		o: function outro(local) {
    			transition_out(if_block);
    			current = false;
    		},
    		d: function destroy(detaching) {
    			if (detaching) detach_dev(div1);
    			if (if_block) if_block.d();
    			mounted = false;
    			dispose();
    		}
    	};

    	dispatch_dev("SvelteRegisterBlock", {
    		block,
    		id: create_if_block_8$1.name,
    		type: "if",
    		source: "(284:4) {#if enterOrSelectExisting === \\\"enter\\\"}",
    		ctx
    	});

    	return block;
    }

    // (300:6) {#if $storage_provider.region_required}
    function create_if_block_9$1(ctx) {
    	let div;
    	let label;
    	let t0_value = /*$strings*/ ctx[14].region + "";
    	let t0;
    	let t1;
    	let definedinwpconfig;
    	let t2;
    	let select;
    	let current;
    	let mounted;
    	let dispose;

    	definedinwpconfig = new DefinedInWPConfig({
    			props: { defined: /*newRegionDefined*/ ctx[3] },
    			$$inline: true
    		});

    	let each_value_3 = Object.entries(/*$storage_provider*/ ctx[13].regions);
    	validate_each_argument(each_value_3);
    	let each_blocks = [];

    	for (let i = 0; i < each_value_3.length; i += 1) {
    		each_blocks[i] = create_each_block_3(get_each_context_3(ctx, each_value_3, i));
    	}

    	const block = {
    		c: function create() {
    			div = element("div");
    			label = element("label");
    			t0 = text(t0_value);
    			t1 = text(" ");
    			create_component(definedinwpconfig.$$.fragment);
    			t2 = space();
    			select = element("select");

    			for (let i = 0; i < each_blocks.length; i += 1) {
    				each_blocks[i].c();
    			}

    			attr_dev(label, "class", "input-label");
    			attr_dev(label, "for", "region");
    			add_location(label, file$f, 301, 8, 8320);
    			attr_dev(select, "name", "region");
    			attr_dev(select, "id", "region");
    			select.disabled = /*newRegionDisabled*/ ctx[12];
    			if (/*newRegion*/ ctx[8] === void 0) add_render_callback(() => /*select_change_handler*/ ctx[26].call(select));
    			toggle_class(select, "disabled", /*newRegionDisabled*/ ctx[12]);
    			add_location(select, file$f, 304, 8, 8466);
    			attr_dev(div, "class", "region flex-column");
    			add_location(div, file$f, 300, 7, 8279);
    		},
    		m: function mount(target, anchor) {
    			insert_dev(target, div, anchor);
    			append_dev(div, label);
    			append_dev(label, t0);
    			append_dev(label, t1);
    			mount_component(definedinwpconfig, label, null);
    			append_dev(div, t2);
    			append_dev(div, select);

    			for (let i = 0; i < each_blocks.length; i += 1) {
    				each_blocks[i].m(select, null);
    			}

    			select_option(select, /*newRegion*/ ctx[8]);
    			current = true;

    			if (!mounted) {
    				dispose = listen_dev(select, "change", /*select_change_handler*/ ctx[26]);
    				mounted = true;
    			}
    		},
    		p: function update(ctx, dirty) {
    			if ((!current || dirty[0] & /*$strings*/ 16384) && t0_value !== (t0_value = /*$strings*/ ctx[14].region + "")) set_data_dev(t0, t0_value);
    			const definedinwpconfig_changes = {};
    			if (dirty[0] & /*newRegionDefined*/ 8) definedinwpconfig_changes.defined = /*newRegionDefined*/ ctx[3];
    			definedinwpconfig.$set(definedinwpconfig_changes);

    			if (dirty[0] & /*$storage_provider, newRegion*/ 8448) {
    				each_value_3 = Object.entries(/*$storage_provider*/ ctx[13].regions);
    				validate_each_argument(each_value_3);
    				let i;

    				for (i = 0; i < each_value_3.length; i += 1) {
    					const child_ctx = get_each_context_3(ctx, each_value_3, i);

    					if (each_blocks[i]) {
    						each_blocks[i].p(child_ctx, dirty);
    					} else {
    						each_blocks[i] = create_each_block_3(child_ctx);
    						each_blocks[i].c();
    						each_blocks[i].m(select, null);
    					}
    				}

    				for (; i < each_blocks.length; i += 1) {
    					each_blocks[i].d(1);
    				}

    				each_blocks.length = each_value_3.length;
    			}

    			if (!current || dirty[0] & /*newRegionDisabled*/ 4096) {
    				prop_dev(select, "disabled", /*newRegionDisabled*/ ctx[12]);
    			}

    			if (dirty[0] & /*newRegion, $storage_provider*/ 8448) {
    				select_option(select, /*newRegion*/ ctx[8]);
    			}

    			if (dirty[0] & /*newRegionDisabled*/ 4096) {
    				toggle_class(select, "disabled", /*newRegionDisabled*/ ctx[12]);
    			}
    		},
    		i: function intro(local) {
    			if (current) return;
    			transition_in(definedinwpconfig.$$.fragment, local);
    			current = true;
    		},
    		o: function outro(local) {
    			transition_out(definedinwpconfig.$$.fragment, local);
    			current = false;
    		},
    		d: function destroy(detaching) {
    			if (detaching) detach_dev(div);
    			destroy_component(definedinwpconfig);
    			destroy_each(each_blocks, detaching);
    			mounted = false;
    			dispose();
    		}
    	};

    	dispatch_dev("SvelteRegisterBlock", {
    		block,
    		id: create_if_block_9$1.name,
    		type: "if",
    		source: "(300:6) {#if $storage_provider.region_required}",
    		ctx
    	});

    	return block;
    }

    // (306:9) {#each Object.entries( $storage_provider.regions ) as [regionKey, regionName], index}
    function create_each_block_3(ctx) {
    	let option;
    	let t0_value = /*regionName*/ ctx[40] + "";
    	let t0;
    	let t1;
    	let option_value_value;
    	let option_selected_value;

    	const block = {
    		c: function create() {
    			option = element("option");
    			t0 = text(t0_value);
    			t1 = space();
    			option.__value = option_value_value = /*regionKey*/ ctx[39];
    			option.value = option.__value;
    			option.selected = option_selected_value = /*regionKey*/ ctx[39] === /*newRegion*/ ctx[8];
    			add_location(option, file$f, 306, 10, 8693);
    		},
    		m: function mount(target, anchor) {
    			insert_dev(target, option, anchor);
    			append_dev(option, t0);
    			append_dev(option, t1);
    		},
    		p: function update(ctx, dirty) {
    			if (dirty[0] & /*$storage_provider*/ 8192 && t0_value !== (t0_value = /*regionName*/ ctx[40] + "")) set_data_dev(t0, t0_value);

    			if (dirty[0] & /*$storage_provider*/ 8192 && option_value_value !== (option_value_value = /*regionKey*/ ctx[39])) {
    				prop_dev(option, "__value", option_value_value);
    				option.value = option.__value;
    			}

    			if (dirty[0] & /*$storage_provider, newRegion*/ 8448 && option_selected_value !== (option_selected_value = /*regionKey*/ ctx[39] === /*newRegion*/ ctx[8])) {
    				prop_dev(option, "selected", option_selected_value);
    			}
    		},
    		d: function destroy(detaching) {
    			if (detaching) detach_dev(option);
    		}
    	};

    	dispatch_dev("SvelteRegisterBlock", {
    		block,
    		id: create_each_block_3.name,
    		type: "each",
    		source: "(306:9) {#each Object.entries( $storage_provider.regions ) as [regionKey, regionName], index}",
    		ctx
    	});

    	return block;
    }

    // (320:4) {#if enterOrSelectExisting === "select"}
    function create_if_block_4$1(ctx) {
    	let t;
    	let await_block_anchor;
    	let promise;
    	let current;
    	let if_block = /*$storage_provider*/ ctx[13].region_required && create_if_block_7$1(ctx);

    	let info = {
    		ctx,
    		current: null,
    		token: null,
    		hasCatch: false,
    		pending: create_pending_block,
    		then: create_then_block,
    		catch: create_catch_block,
    		value: 43,
    		blocks: [,,,]
    	};

    	handle_promise(promise = /*getBuckets*/ ctx[18](/*newRegion*/ ctx[8]), info);

    	const block = {
    		c: function create() {
    			if (if_block) if_block.c();
    			t = space();
    			await_block_anchor = empty();
    			info.block.c();
    		},
    		m: function mount(target, anchor) {
    			if (if_block) if_block.m(target, anchor);
    			insert_dev(target, t, anchor);
    			insert_dev(target, await_block_anchor, anchor);
    			info.block.m(target, info.anchor = anchor);
    			info.mount = () => await_block_anchor.parentNode;
    			info.anchor = await_block_anchor;
    			current = true;
    		},
    		p: function update(new_ctx, dirty) {
    			ctx = new_ctx;

    			if (/*$storage_provider*/ ctx[13].region_required) {
    				if (if_block) {
    					if_block.p(ctx, dirty);

    					if (dirty[0] & /*$storage_provider*/ 8192) {
    						transition_in(if_block, 1);
    					}
    				} else {
    					if_block = create_if_block_7$1(ctx);
    					if_block.c();
    					transition_in(if_block, 1);
    					if_block.m(t.parentNode, t);
    				}
    			} else if (if_block) {
    				group_outros();

    				transition_out(if_block, 1, 1, () => {
    					if_block = null;
    				});

    				check_outros();
    			}

    			info.ctx = ctx;

    			if (dirty[0] & /*newRegion*/ 256 && promise !== (promise = /*getBuckets*/ ctx[18](/*newRegion*/ ctx[8])) && handle_promise(promise, info)) ; else {
    				update_await_block_branch(info, ctx, dirty);
    			}
    		},
    		i: function intro(local) {
    			if (current) return;
    			transition_in(if_block);
    			transition_in(info.block);
    			current = true;
    		},
    		o: function outro(local) {
    			transition_out(if_block);

    			for (let i = 0; i < 3; i += 1) {
    				const block = info.blocks[i];
    				transition_out(block);
    			}

    			current = false;
    		},
    		d: function destroy(detaching) {
    			if (if_block) if_block.d(detaching);
    			if (detaching) detach_dev(t);
    			if (detaching) detach_dev(await_block_anchor);
    			info.block.d(detaching);
    			info.token = null;
    			info = null;
    		}
    	};

    	dispatch_dev("SvelteRegisterBlock", {
    		block,
    		id: create_if_block_4$1.name,
    		type: "if",
    		source: "(320:4) {#if enterOrSelectExisting === \\\"select\\\"}",
    		ctx
    	});

    	return block;
    }

    // (321:5) {#if $storage_provider.region_required}
    function create_if_block_7$1(ctx) {
    	let label;
    	let t0_value = /*$strings*/ ctx[14].region + "";
    	let t0;
    	let t1;
    	let definedinwpconfig;
    	let t2;
    	let select;
    	let current;
    	let mounted;
    	let dispose;

    	definedinwpconfig = new DefinedInWPConfig({
    			props: { defined: /*newRegionDefined*/ ctx[3] },
    			$$inline: true
    		});

    	let each_value_2 = Object.entries(/*$storage_provider*/ ctx[13].regions);
    	validate_each_argument(each_value_2);
    	let each_blocks = [];

    	for (let i = 0; i < each_value_2.length; i += 1) {
    		each_blocks[i] = create_each_block_2(get_each_context_2(ctx, each_value_2, i));
    	}

    	const block = {
    		c: function create() {
    			label = element("label");
    			t0 = text(t0_value);
    			t1 = text(" ");
    			create_component(definedinwpconfig.$$.fragment);
    			t2 = space();
    			select = element("select");

    			for (let i = 0; i < each_blocks.length; i += 1) {
    				each_blocks[i].c();
    			}

    			attr_dev(label, "class", "input-label");
    			attr_dev(label, "for", "list-region");
    			add_location(label, file$f, 321, 6, 9012);
    			attr_dev(select, "name", "region");
    			attr_dev(select, "id", "list-region");
    			select.disabled = /*newRegionDisabled*/ ctx[12];
    			if (/*newRegion*/ ctx[8] === void 0) add_render_callback(() => /*select_change_handler_1*/ ctx[27].call(select));
    			toggle_class(select, "disabled", /*newRegionDisabled*/ ctx[12]);
    			add_location(select, file$f, 324, 6, 9157);
    		},
    		m: function mount(target, anchor) {
    			insert_dev(target, label, anchor);
    			append_dev(label, t0);
    			append_dev(label, t1);
    			mount_component(definedinwpconfig, label, null);
    			insert_dev(target, t2, anchor);
    			insert_dev(target, select, anchor);

    			for (let i = 0; i < each_blocks.length; i += 1) {
    				each_blocks[i].m(select, null);
    			}

    			select_option(select, /*newRegion*/ ctx[8]);
    			current = true;

    			if (!mounted) {
    				dispose = listen_dev(select, "change", /*select_change_handler_1*/ ctx[27]);
    				mounted = true;
    			}
    		},
    		p: function update(ctx, dirty) {
    			if ((!current || dirty[0] & /*$strings*/ 16384) && t0_value !== (t0_value = /*$strings*/ ctx[14].region + "")) set_data_dev(t0, t0_value);
    			const definedinwpconfig_changes = {};
    			if (dirty[0] & /*newRegionDefined*/ 8) definedinwpconfig_changes.defined = /*newRegionDefined*/ ctx[3];
    			definedinwpconfig.$set(definedinwpconfig_changes);

    			if (dirty[0] & /*$storage_provider, newRegion*/ 8448) {
    				each_value_2 = Object.entries(/*$storage_provider*/ ctx[13].regions);
    				validate_each_argument(each_value_2);
    				let i;

    				for (i = 0; i < each_value_2.length; i += 1) {
    					const child_ctx = get_each_context_2(ctx, each_value_2, i);

    					if (each_blocks[i]) {
    						each_blocks[i].p(child_ctx, dirty);
    					} else {
    						each_blocks[i] = create_each_block_2(child_ctx);
    						each_blocks[i].c();
    						each_blocks[i].m(select, null);
    					}
    				}

    				for (; i < each_blocks.length; i += 1) {
    					each_blocks[i].d(1);
    				}

    				each_blocks.length = each_value_2.length;
    			}

    			if (!current || dirty[0] & /*newRegionDisabled*/ 4096) {
    				prop_dev(select, "disabled", /*newRegionDisabled*/ ctx[12]);
    			}

    			if (dirty[0] & /*newRegion, $storage_provider*/ 8448) {
    				select_option(select, /*newRegion*/ ctx[8]);
    			}

    			if (dirty[0] & /*newRegionDisabled*/ 4096) {
    				toggle_class(select, "disabled", /*newRegionDisabled*/ ctx[12]);
    			}
    		},
    		i: function intro(local) {
    			if (current) return;
    			transition_in(definedinwpconfig.$$.fragment, local);
    			current = true;
    		},
    		o: function outro(local) {
    			transition_out(definedinwpconfig.$$.fragment, local);
    			current = false;
    		},
    		d: function destroy(detaching) {
    			if (detaching) detach_dev(label);
    			destroy_component(definedinwpconfig);
    			if (detaching) detach_dev(t2);
    			if (detaching) detach_dev(select);
    			destroy_each(each_blocks, detaching);
    			mounted = false;
    			dispose();
    		}
    	};

    	dispatch_dev("SvelteRegisterBlock", {
    		block,
    		id: create_if_block_7$1.name,
    		type: "if",
    		source: "(321:5) {#if $storage_provider.region_required}",
    		ctx
    	});

    	return block;
    }

    // (326:7) {#each Object.entries( $storage_provider.regions ) as [regionKey, regionName], index}
    function create_each_block_2(ctx) {
    	let option;
    	let t0_value = /*regionName*/ ctx[40] + "";
    	let t0;
    	let t1;
    	let option_value_value;
    	let option_selected_value;

    	const block = {
    		c: function create() {
    			option = element("option");
    			t0 = text(t0_value);
    			t1 = space();
    			option.__value = option_value_value = /*regionKey*/ ctx[39];
    			option.value = option.__value;
    			option.selected = option_selected_value = /*regionKey*/ ctx[39] === /*newRegion*/ ctx[8];
    			add_location(option, file$f, 326, 8, 9385);
    		},
    		m: function mount(target, anchor) {
    			insert_dev(target, option, anchor);
    			append_dev(option, t0);
    			append_dev(option, t1);
    		},
    		p: function update(ctx, dirty) {
    			if (dirty[0] & /*$storage_provider*/ 8192 && t0_value !== (t0_value = /*regionName*/ ctx[40] + "")) set_data_dev(t0, t0_value);

    			if (dirty[0] & /*$storage_provider*/ 8192 && option_value_value !== (option_value_value = /*regionKey*/ ctx[39])) {
    				prop_dev(option, "__value", option_value_value);
    				option.value = option.__value;
    			}

    			if (dirty[0] & /*$storage_provider, newRegion*/ 8448 && option_selected_value !== (option_selected_value = /*regionKey*/ ctx[39] === /*newRegion*/ ctx[8])) {
    				prop_dev(option, "selected", option_selected_value);
    			}
    		},
    		d: function destroy(detaching) {
    			if (detaching) detach_dev(option);
    		}
    	};

    	dispatch_dev("SvelteRegisterBlock", {
    		block,
    		id: create_each_block_2.name,
    		type: "each",
    		source: "(326:7) {#each Object.entries( $storage_provider.regions ) as [regionKey, regionName], index}",
    		ctx
    	});

    	return block;
    }

    // (1:0) <script>  import {   createEventDispatcher,   getContext,   hasContext,   onMount  }
    function create_catch_block(ctx) {
    	const block = {
    		c: noop,
    		m: noop,
    		p: noop,
    		i: noop,
    		o: noop,
    		d: noop
    	};

    	dispatch_dev("SvelteRegisterBlock", {
    		block,
    		id: create_catch_block.name,
    		type: "catch",
    		source: "(1:0) <script>  import {   createEventDispatcher,   getContext,   hasContext,   onMount  }",
    		ctx
    	});

    	return block;
    }

    // (338:5) {:then buckets}
    function create_then_block(ctx) {
    	let ul;

    	function select_block_type(ctx, dirty) {
    		if (/*buckets*/ ctx[43].length) return create_if_block_5$1;
    		return create_else_block$1;
    	}

    	let current_block_type = select_block_type(ctx);
    	let if_block = current_block_type(ctx);

    	const block = {
    		c: function create() {
    			ul = element("ul");
    			if_block.c();
    			attr_dev(ul, "class", "bucket-list");
    			add_location(ul, file$f, 338, 6, 9638);
    		},
    		m: function mount(target, anchor) {
    			insert_dev(target, ul, anchor);
    			if_block.m(ul, null);
    		},
    		p: function update(ctx, dirty) {
    			if (current_block_type === (current_block_type = select_block_type(ctx)) && if_block) {
    				if_block.p(ctx, dirty);
    			} else {
    				if_block.d(1);
    				if_block = current_block_type(ctx);

    				if (if_block) {
    					if_block.c();
    					if_block.m(ul, null);
    				}
    			}
    		},
    		i: noop,
    		o: noop,
    		d: function destroy(detaching) {
    			if (detaching) detach_dev(ul);
    			if_block.d();
    		}
    	};

    	dispatch_dev("SvelteRegisterBlock", {
    		block,
    		id: create_then_block.name,
    		type: "then",
    		source: "(338:5) {:then buckets}",
    		ctx
    	});

    	return block;
    }

    // (356:7) {:else}
    function create_else_block$1(ctx) {
    	let li;
    	let p;
    	let t_value = /*$strings*/ ctx[14].nothing_found + "";
    	let t;

    	const block = {
    		c: function create() {
    			li = element("li");
    			p = element("p");
    			t = text(t_value);
    			add_location(p, file$f, 357, 9, 10403);
    			attr_dev(li, "class", "row nothing-found");
    			add_location(li, file$f, 356, 8, 10363);
    		},
    		m: function mount(target, anchor) {
    			insert_dev(target, li, anchor);
    			append_dev(li, p);
    			append_dev(p, t);
    		},
    		p: function update(ctx, dirty) {
    			if (dirty[0] & /*$strings*/ 16384 && t_value !== (t_value = /*$strings*/ ctx[14].nothing_found + "")) set_data_dev(t, t_value);
    		},
    		d: function destroy(detaching) {
    			if (detaching) detach_dev(li);
    		}
    	};

    	dispatch_dev("SvelteRegisterBlock", {
    		block,
    		id: create_else_block$1.name,
    		type: "else",
    		source: "(356:7) {:else}",
    		ctx
    	});

    	return block;
    }

    // (340:7) {#if buckets.length}
    function create_if_block_5$1(ctx) {
    	let each_1_anchor;
    	let each_value_1 = /*buckets*/ ctx[43];
    	validate_each_argument(each_value_1);
    	let each_blocks = [];

    	for (let i = 0; i < each_value_1.length; i += 1) {
    		each_blocks[i] = create_each_block_1(get_each_context_1(ctx, each_value_1, i));
    	}

    	const block = {
    		c: function create() {
    			for (let i = 0; i < each_blocks.length; i += 1) {
    				each_blocks[i].c();
    			}

    			each_1_anchor = empty();
    		},
    		m: function mount(target, anchor) {
    			for (let i = 0; i < each_blocks.length; i += 1) {
    				each_blocks[i].m(target, anchor);
    			}

    			insert_dev(target, each_1_anchor, anchor);
    		},
    		p: function update(ctx, dirty) {
    			if (dirty[0] & /*getBuckets, newRegion, newBucket, $urls, $strings*/ 311556) {
    				each_value_1 = /*buckets*/ ctx[43];
    				validate_each_argument(each_value_1);
    				let i;

    				for (i = 0; i < each_value_1.length; i += 1) {
    					const child_ctx = get_each_context_1(ctx, each_value_1, i);

    					if (each_blocks[i]) {
    						each_blocks[i].p(child_ctx, dirty);
    					} else {
    						each_blocks[i] = create_each_block_1(child_ctx);
    						each_blocks[i].c();
    						each_blocks[i].m(each_1_anchor.parentNode, each_1_anchor);
    					}
    				}

    				for (; i < each_blocks.length; i += 1) {
    					each_blocks[i].d(1);
    				}

    				each_blocks.length = each_value_1.length;
    			}
    		},
    		d: function destroy(detaching) {
    			destroy_each(each_blocks, detaching);
    			if (detaching) detach_dev(each_1_anchor);
    		}
    	};

    	dispatch_dev("SvelteRegisterBlock", {
    		block,
    		id: create_if_block_5$1.name,
    		type: "if",
    		source: "(340:7) {#if buckets.length}",
    		ctx
    	});

    	return block;
    }

    // (351:10) {#if newBucket === bucket.Name}
    function create_if_block_6$1(ctx) {
    	let img;
    	let img_src_value;
    	let img_alt_value;

    	const block = {
    		c: function create() {
    			img = element("img");
    			attr_dev(img, "class", "icon status");
    			if (!src_url_equal(img.src, img_src_value = /*$urls*/ ctx[15].assets + 'img/icon/licence-checked.svg')) attr_dev(img, "src", img_src_value);
    			attr_dev(img, "type", "image/svg+xml");
    			attr_dev(img, "alt", img_alt_value = /*$strings*/ ctx[14].selected_desc);
    			add_location(img, file$f, 351, 11, 10163);
    		},
    		m: function mount(target, anchor) {
    			insert_dev(target, img, anchor);
    		},
    		p: function update(ctx, dirty) {
    			if (dirty[0] & /*$urls*/ 32768 && !src_url_equal(img.src, img_src_value = /*$urls*/ ctx[15].assets + 'img/icon/licence-checked.svg')) {
    				attr_dev(img, "src", img_src_value);
    			}

    			if (dirty[0] & /*$strings*/ 16384 && img_alt_value !== (img_alt_value = /*$strings*/ ctx[14].selected_desc)) {
    				attr_dev(img, "alt", img_alt_value);
    			}
    		},
    		d: function destroy(detaching) {
    			if (detaching) detach_dev(img);
    		}
    	};

    	dispatch_dev("SvelteRegisterBlock", {
    		block,
    		id: create_if_block_6$1.name,
    		type: "if",
    		source: "(351:10) {#if newBucket === bucket.Name}",
    		ctx
    	});

    	return block;
    }

    // (341:8) {#each buckets as bucket}
    function create_each_block_1(ctx) {
    	let li;
    	let img;
    	let img_src_value;
    	let img_alt_value;
    	let t0;
    	let p;
    	let t1_value = /*bucket*/ ctx[44].Name + "";
    	let t1;
    	let t2;
    	let t3;
    	let li_data_bucket_name_value;
    	let scrollIntoView_action;
    	let mounted;
    	let dispose;
    	let if_block = /*newBucket*/ ctx[2] === /*bucket*/ ctx[44].Name && create_if_block_6$1(ctx);

    	function click_handler() {
    		return /*click_handler*/ ctx[28](/*bucket*/ ctx[44]);
    	}

    	const block = {
    		c: function create() {
    			li = element("li");
    			img = element("img");
    			t0 = space();
    			p = element("p");
    			t1 = text(t1_value);
    			t2 = space();
    			if (if_block) if_block.c();
    			t3 = space();
    			attr_dev(img, "class", "icon bucket");
    			if (!src_url_equal(img.src, img_src_value = /*$urls*/ ctx[15].assets + 'img/icon/bucket.svg')) attr_dev(img, "src", img_src_value);
    			attr_dev(img, "alt", img_alt_value = /*$strings*/ ctx[14].bucket_icon);
    			add_location(img, file$f, 348, 10, 9981);
    			add_location(p, file$f, 349, 10, 10089);
    			attr_dev(li, "class", "row");
    			attr_dev(li, "data-bucket-name", li_data_bucket_name_value = /*bucket*/ ctx[44].Name);
    			toggle_class(li, "active", /*newBucket*/ ctx[2] === /*bucket*/ ctx[44].Name);
    			add_location(li, file$f, 341, 9, 9734);
    		},
    		m: function mount(target, anchor) {
    			insert_dev(target, li, anchor);
    			append_dev(li, img);
    			append_dev(li, t0);
    			append_dev(li, p);
    			append_dev(p, t1);
    			append_dev(li, t2);
    			if (if_block) if_block.m(li, null);
    			append_dev(li, t3);

    			if (!mounted) {
    				dispose = [
    					listen_dev(li, "click", click_handler, false, false, false),
    					action_destroyer(scrollIntoView_action = scrollIntoView.call(null, li, /*newBucket*/ ctx[2] === /*bucket*/ ctx[44].Name))
    				];

    				mounted = true;
    			}
    		},
    		p: function update(new_ctx, dirty) {
    			ctx = new_ctx;

    			if (dirty[0] & /*$urls*/ 32768 && !src_url_equal(img.src, img_src_value = /*$urls*/ ctx[15].assets + 'img/icon/bucket.svg')) {
    				attr_dev(img, "src", img_src_value);
    			}

    			if (dirty[0] & /*$strings*/ 16384 && img_alt_value !== (img_alt_value = /*$strings*/ ctx[14].bucket_icon)) {
    				attr_dev(img, "alt", img_alt_value);
    			}

    			if (dirty[0] & /*newRegion*/ 256 && t1_value !== (t1_value = /*bucket*/ ctx[44].Name + "")) set_data_dev(t1, t1_value);

    			if (/*newBucket*/ ctx[2] === /*bucket*/ ctx[44].Name) {
    				if (if_block) {
    					if_block.p(ctx, dirty);
    				} else {
    					if_block = create_if_block_6$1(ctx);
    					if_block.c();
    					if_block.m(li, t3);
    				}
    			} else if (if_block) {
    				if_block.d(1);
    				if_block = null;
    			}

    			if (dirty[0] & /*newRegion, $storage_provider*/ 8448 && li_data_bucket_name_value !== (li_data_bucket_name_value = /*bucket*/ ctx[44].Name)) {
    				attr_dev(li, "data-bucket-name", li_data_bucket_name_value);
    			}

    			if (scrollIntoView_action && is_function(scrollIntoView_action.update) && dirty[0] & /*newBucket, newRegion*/ 260) scrollIntoView_action.update.call(null, /*newBucket*/ ctx[2] === /*bucket*/ ctx[44].Name);

    			if (dirty[0] & /*newBucket, getBuckets, newRegion*/ 262404) {
    				toggle_class(li, "active", /*newBucket*/ ctx[2] === /*bucket*/ ctx[44].Name);
    			}
    		},
    		d: function destroy(detaching) {
    			if (detaching) detach_dev(li);
    			if (if_block) if_block.d();
    			mounted = false;
    			run_all(dispose);
    		}
    	};

    	dispatch_dev("SvelteRegisterBlock", {
    		block,
    		id: create_each_block_1.name,
    		type: "each",
    		source: "(341:8) {#each buckets as bucket}",
    		ctx
    	});

    	return block;
    }

    // (336:37)        <Loading/>      {:then buckets}
    function create_pending_block(ctx) {
    	let loading;
    	let current;
    	loading = new Loading({ $$inline: true });

    	const block = {
    		c: function create() {
    			create_component(loading.$$.fragment);
    		},
    		m: function mount(target, anchor) {
    			mount_component(loading, target, anchor);
    			current = true;
    		},
    		p: noop,
    		i: function intro(local) {
    			if (current) return;
    			transition_in(loading.$$.fragment, local);
    			current = true;
    		},
    		o: function outro(local) {
    			transition_out(loading.$$.fragment, local);
    			current = false;
    		},
    		d: function destroy(detaching) {
    			destroy_component(loading, detaching);
    		}
    	};

    	dispatch_dev("SvelteRegisterBlock", {
    		block,
    		id: create_pending_block.name,
    		type: "pending",
    		source: "(336:37)        <Loading/>      {:then buckets}",
    		ctx
    	});

    	return block;
    }

    // (364:4) {#if invalidBucketNameMessage}
    function create_if_block_3$1(ctx) {
    	let p;
    	let t;
    	let p_transition;
    	let current;

    	const block = {
    		c: function create() {
    			p = element("p");
    			t = text(/*invalidBucketNameMessage*/ ctx[10]);
    			attr_dev(p, "class", "input-error");
    			add_location(p, file$f, 364, 5, 10538);
    		},
    		m: function mount(target, anchor) {
    			insert_dev(target, p, anchor);
    			append_dev(p, t);
    			current = true;
    		},
    		p: function update(ctx, dirty) {
    			if (!current || dirty[0] & /*invalidBucketNameMessage*/ 1024) set_data_dev(t, /*invalidBucketNameMessage*/ ctx[10]);
    		},
    		i: function intro(local) {
    			if (current) return;

    			if (local) {
    				add_render_callback(() => {
    					if (!p_transition) p_transition = create_bidirectional_transition(p, slide, {}, true);
    					p_transition.run(1);
    				});
    			}

    			current = true;
    		},
    		o: function outro(local) {
    			if (local) {
    				if (!p_transition) p_transition = create_bidirectional_transition(p, slide, {}, false);
    				p_transition.run(0);
    			}

    			current = false;
    		},
    		d: function destroy(detaching) {
    			if (detaching) detach_dev(p);
    			if (detaching && p_transition) p_transition.end();
    		}
    	};

    	dispatch_dev("SvelteRegisterBlock", {
    		block,
    		id: create_if_block_3$1.name,
    		type: "if",
    		source: "(364:4) {#if invalidBucketNameMessage}",
    		ctx
    	});

    	return block;
    }

    // (278:3) <PanelRow class="body flex-column">
    function create_default_slot_4$2(ctx) {
    	let div;
    	let radiobutton0;
    	let updating_selected;
    	let t0;
    	let radiobutton1;
    	let updating_selected_1;
    	let t1;
    	let t2;
    	let t3;
    	let if_block2_anchor;
    	let current;

    	function radiobutton0_selected_binding(value) {
    		/*radiobutton0_selected_binding*/ ctx[23](value);
    	}

    	let radiobutton0_props = {
    		value: "enter",
    		list: true,
    		disabled: /*disabled*/ ctx[11],
    		$$slots: { default: [create_default_slot_6$1] },
    		$$scope: { ctx }
    	};

    	if (/*enterOrSelectExisting*/ ctx[1] !== void 0) {
    		radiobutton0_props.selected = /*enterOrSelectExisting*/ ctx[1];
    	}

    	radiobutton0 = new RadioButton({
    			props: radiobutton0_props,
    			$$inline: true
    		});

    	binding_callbacks.push(() => bind(radiobutton0, 'selected', radiobutton0_selected_binding));

    	function radiobutton1_selected_binding(value) {
    		/*radiobutton1_selected_binding*/ ctx[24](value);
    	}

    	let radiobutton1_props = {
    		value: "select",
    		list: true,
    		disabled: /*disabled*/ ctx[11],
    		$$slots: { default: [create_default_slot_5$1] },
    		$$scope: { ctx }
    	};

    	if (/*enterOrSelectExisting*/ ctx[1] !== void 0) {
    		radiobutton1_props.selected = /*enterOrSelectExisting*/ ctx[1];
    	}

    	radiobutton1 = new RadioButton({
    			props: radiobutton1_props,
    			$$inline: true
    		});

    	binding_callbacks.push(() => bind(radiobutton1, 'selected', radiobutton1_selected_binding));
    	let if_block0 = /*enterOrSelectExisting*/ ctx[1] === "enter" && create_if_block_8$1(ctx);
    	let if_block1 = /*enterOrSelectExisting*/ ctx[1] === "select" && create_if_block_4$1(ctx);
    	let if_block2 = /*invalidBucketNameMessage*/ ctx[10] && create_if_block_3$1(ctx);

    	const block = {
    		c: function create() {
    			div = element("div");
    			create_component(radiobutton0.$$.fragment);
    			t0 = space();
    			create_component(radiobutton1.$$.fragment);
    			t1 = space();
    			if (if_block0) if_block0.c();
    			t2 = space();
    			if (if_block1) if_block1.c();
    			t3 = space();
    			if (if_block2) if_block2.c();
    			if_block2_anchor = empty();
    			attr_dev(div, "class", "flex-row align-center row radio-btns");
    			add_location(div, file$f, 278, 4, 7403);
    		},
    		m: function mount(target, anchor) {
    			insert_dev(target, div, anchor);
    			mount_component(radiobutton0, div, null);
    			append_dev(div, t0);
    			mount_component(radiobutton1, div, null);
    			insert_dev(target, t1, anchor);
    			if (if_block0) if_block0.m(target, anchor);
    			insert_dev(target, t2, anchor);
    			if (if_block1) if_block1.m(target, anchor);
    			insert_dev(target, t3, anchor);
    			if (if_block2) if_block2.m(target, anchor);
    			insert_dev(target, if_block2_anchor, anchor);
    			current = true;
    		},
    		p: function update(ctx, dirty) {
    			const radiobutton0_changes = {};
    			if (dirty[0] & /*disabled*/ 2048) radiobutton0_changes.disabled = /*disabled*/ ctx[11];

    			if (dirty[0] & /*$strings*/ 16384 | dirty[1] & /*$$scope*/ 262144) {
    				radiobutton0_changes.$$scope = { dirty, ctx };
    			}

    			if (!updating_selected && dirty[0] & /*enterOrSelectExisting*/ 2) {
    				updating_selected = true;
    				radiobutton0_changes.selected = /*enterOrSelectExisting*/ ctx[1];
    				add_flush_callback(() => updating_selected = false);
    			}

    			radiobutton0.$set(radiobutton0_changes);
    			const radiobutton1_changes = {};
    			if (dirty[0] & /*disabled*/ 2048) radiobutton1_changes.disabled = /*disabled*/ ctx[11];

    			if (dirty[0] & /*$strings*/ 16384 | dirty[1] & /*$$scope*/ 262144) {
    				radiobutton1_changes.$$scope = { dirty, ctx };
    			}

    			if (!updating_selected_1 && dirty[0] & /*enterOrSelectExisting*/ 2) {
    				updating_selected_1 = true;
    				radiobutton1_changes.selected = /*enterOrSelectExisting*/ ctx[1];
    				add_flush_callback(() => updating_selected_1 = false);
    			}

    			radiobutton1.$set(radiobutton1_changes);

    			if (/*enterOrSelectExisting*/ ctx[1] === "enter") {
    				if (if_block0) {
    					if_block0.p(ctx, dirty);

    					if (dirty[0] & /*enterOrSelectExisting*/ 2) {
    						transition_in(if_block0, 1);
    					}
    				} else {
    					if_block0 = create_if_block_8$1(ctx);
    					if_block0.c();
    					transition_in(if_block0, 1);
    					if_block0.m(t2.parentNode, t2);
    				}
    			} else if (if_block0) {
    				group_outros();

    				transition_out(if_block0, 1, 1, () => {
    					if_block0 = null;
    				});

    				check_outros();
    			}

    			if (/*enterOrSelectExisting*/ ctx[1] === "select") {
    				if (if_block1) {
    					if_block1.p(ctx, dirty);

    					if (dirty[0] & /*enterOrSelectExisting*/ 2) {
    						transition_in(if_block1, 1);
    					}
    				} else {
    					if_block1 = create_if_block_4$1(ctx);
    					if_block1.c();
    					transition_in(if_block1, 1);
    					if_block1.m(t3.parentNode, t3);
    				}
    			} else if (if_block1) {
    				group_outros();

    				transition_out(if_block1, 1, 1, () => {
    					if_block1 = null;
    				});

    				check_outros();
    			}

    			if (/*invalidBucketNameMessage*/ ctx[10]) {
    				if (if_block2) {
    					if_block2.p(ctx, dirty);

    					if (dirty[0] & /*invalidBucketNameMessage*/ 1024) {
    						transition_in(if_block2, 1);
    					}
    				} else {
    					if_block2 = create_if_block_3$1(ctx);
    					if_block2.c();
    					transition_in(if_block2, 1);
    					if_block2.m(if_block2_anchor.parentNode, if_block2_anchor);
    				}
    			} else if (if_block2) {
    				group_outros();

    				transition_out(if_block2, 1, 1, () => {
    					if_block2 = null;
    				});

    				check_outros();
    			}
    		},
    		i: function intro(local) {
    			if (current) return;
    			transition_in(radiobutton0.$$.fragment, local);
    			transition_in(radiobutton1.$$.fragment, local);
    			transition_in(if_block0);
    			transition_in(if_block1);
    			transition_in(if_block2);
    			current = true;
    		},
    		o: function outro(local) {
    			transition_out(radiobutton0.$$.fragment, local);
    			transition_out(radiobutton1.$$.fragment, local);
    			transition_out(if_block0);
    			transition_out(if_block1);
    			transition_out(if_block2);
    			current = false;
    		},
    		d: function destroy(detaching) {
    			if (detaching) detach_dev(div);
    			destroy_component(radiobutton0);
    			destroy_component(radiobutton1);
    			if (detaching) detach_dev(t1);
    			if (if_block0) if_block0.d(detaching);
    			if (detaching) detach_dev(t2);
    			if (if_block1) if_block1.d(detaching);
    			if (detaching) detach_dev(t3);
    			if (if_block2) if_block2.d(detaching);
    			if (detaching) detach_dev(if_block2_anchor);
    		}
    	};

    	dispatch_dev("SvelteRegisterBlock", {
    		block,
    		id: create_default_slot_4$2.name,
    		type: "slot",
    		source: "(278:3) <PanelRow class=\\\"body flex-column\\\">",
    		ctx
    	});

    	return block;
    }

    // (277:2) <Panel heading={$strings.existing_bucket_title} storageProvider={$storage_provider} multi {defined}>
    function create_default_slot_3$2(ctx) {
    	let panelrow;
    	let current;

    	panelrow = new PanelRow({
    			props: {
    				class: "body flex-column",
    				$$slots: { default: [create_default_slot_4$2] },
    				$$scope: { ctx }
    			},
    			$$inline: true
    		});

    	const block = {
    		c: function create() {
    			create_component(panelrow.$$.fragment);
    		},
    		m: function mount(target, anchor) {
    			mount_component(panelrow, target, anchor);
    			current = true;
    		},
    		p: function update(ctx, dirty) {
    			const panelrow_changes = {};

    			if (dirty[0] & /*invalidBucketNameMessage, newRegion, newBucket, $urls, $strings, newRegionDisabled, $storage_provider, newRegionDefined, enterOrSelectExisting, disabled*/ 64782 | dirty[1] & /*$$scope*/ 262144) {
    				panelrow_changes.$$scope = { dirty, ctx };
    			}

    			panelrow.$set(panelrow_changes);
    		},
    		i: function intro(local) {
    			if (current) return;
    			transition_in(panelrow.$$.fragment, local);
    			current = true;
    		},
    		o: function outro(local) {
    			transition_out(panelrow.$$.fragment, local);
    			current = false;
    		},
    		d: function destroy(detaching) {
    			destroy_component(panelrow, detaching);
    		}
    	};

    	dispatch_dev("SvelteRegisterBlock", {
    		block,
    		id: create_default_slot_3$2.name,
    		type: "slot",
    		source: "(277:2) <Panel heading={$strings.existing_bucket_title} storageProvider={$storage_provider} multi {defined}>",
    		ctx
    	});

    	return block;
    }

    // (371:1) {#if bucketSource === "new"}
    function create_if_block$5(ctx) {
    	let panel;
    	let current;

    	panel = new Panel({
    			props: {
    				heading: /*$strings*/ ctx[14].new_bucket_title,
    				storageProvider: /*$storage_provider*/ ctx[13],
    				multi: true,
    				defined: /*defined*/ ctx[4],
    				$$slots: { default: [create_default_slot_1$2] },
    				$$scope: { ctx }
    			},
    			$$inline: true
    		});

    	const block = {
    		c: function create() {
    			create_component(panel.$$.fragment);
    		},
    		m: function mount(target, anchor) {
    			mount_component(panel, target, anchor);
    			current = true;
    		},
    		p: function update(ctx, dirty) {
    			const panel_changes = {};
    			if (dirty[0] & /*$strings*/ 16384) panel_changes.heading = /*$strings*/ ctx[14].new_bucket_title;
    			if (dirty[0] & /*$storage_provider*/ 8192) panel_changes.storageProvider = /*$storage_provider*/ ctx[13];
    			if (dirty[0] & /*defined*/ 16) panel_changes.defined = /*defined*/ ctx[4];

    			if (dirty[0] & /*invalidBucketNameMessage, newRegionDisabled, newRegion, $storage_provider, newRegionDefined, $strings, disabled, newBucket*/ 32012 | dirty[1] & /*$$scope*/ 262144) {
    				panel_changes.$$scope = { dirty, ctx };
    			}

    			panel.$set(panel_changes);
    		},
    		i: function intro(local) {
    			if (current) return;
    			transition_in(panel.$$.fragment, local);
    			current = true;
    		},
    		o: function outro(local) {
    			transition_out(panel.$$.fragment, local);
    			current = false;
    		},
    		d: function destroy(detaching) {
    			destroy_component(panel, detaching);
    		}
    	};

    	dispatch_dev("SvelteRegisterBlock", {
    		block,
    		id: create_if_block$5.name,
    		type: "if",
    		source: "(371:1) {#if bucketSource === \\\"new\\\"}",
    		ctx
    	});

    	return block;
    }

    // (394:7) {#each Object.entries( $storage_provider.regions ) as [regionKey, regionName], index}
    function create_each_block$3(ctx) {
    	let option;
    	let t0_value = /*regionName*/ ctx[40] + "";
    	let t0;
    	let t1;
    	let option_value_value;
    	let option_selected_value;

    	const block = {
    		c: function create() {
    			option = element("option");
    			t0 = text(t0_value);
    			t1 = space();
    			option.__value = option_value_value = /*regionKey*/ ctx[39];
    			option.value = option.__value;
    			option.selected = option_selected_value = /*regionKey*/ ctx[39] === /*newRegion*/ ctx[8];
    			add_location(option, file$f, 394, 8, 11700);
    		},
    		m: function mount(target, anchor) {
    			insert_dev(target, option, anchor);
    			append_dev(option, t0);
    			append_dev(option, t1);
    		},
    		p: function update(ctx, dirty) {
    			if (dirty[0] & /*$storage_provider*/ 8192 && t0_value !== (t0_value = /*regionName*/ ctx[40] + "")) set_data_dev(t0, t0_value);

    			if (dirty[0] & /*$storage_provider*/ 8192 && option_value_value !== (option_value_value = /*regionKey*/ ctx[39])) {
    				prop_dev(option, "__value", option_value_value);
    				option.value = option.__value;
    			}

    			if (dirty[0] & /*$storage_provider, newRegion*/ 8448 && option_selected_value !== (option_selected_value = /*regionKey*/ ctx[39] === /*newRegion*/ ctx[8])) {
    				prop_dev(option, "selected", option_selected_value);
    			}
    		},
    		d: function destroy(detaching) {
    			if (detaching) detach_dev(option);
    		}
    	};

    	dispatch_dev("SvelteRegisterBlock", {
    		block,
    		id: create_each_block$3.name,
    		type: "each",
    		source: "(394:7) {#each Object.entries( $storage_provider.regions ) as [regionKey, regionName], index}",
    		ctx
    	});

    	return block;
    }

    // (405:4) {#if invalidBucketNameMessage}
    function create_if_block_1$2(ctx) {
    	let p;
    	let t;
    	let p_transition;
    	let current;

    	const block = {
    		c: function create() {
    			p = element("p");
    			t = text(/*invalidBucketNameMessage*/ ctx[10]);
    			attr_dev(p, "class", "input-error");
    			add_location(p, file$f, 405, 5, 11923);
    		},
    		m: function mount(target, anchor) {
    			insert_dev(target, p, anchor);
    			append_dev(p, t);
    			current = true;
    		},
    		p: function update(ctx, dirty) {
    			if (!current || dirty[0] & /*invalidBucketNameMessage*/ 1024) set_data_dev(t, /*invalidBucketNameMessage*/ ctx[10]);
    		},
    		i: function intro(local) {
    			if (current) return;

    			if (local) {
    				add_render_callback(() => {
    					if (!p_transition) p_transition = create_bidirectional_transition(p, slide, {}, true);
    					p_transition.run(1);
    				});
    			}

    			current = true;
    		},
    		o: function outro(local) {
    			if (local) {
    				if (!p_transition) p_transition = create_bidirectional_transition(p, slide, {}, false);
    				p_transition.run(0);
    			}

    			current = false;
    		},
    		d: function destroy(detaching) {
    			if (detaching) detach_dev(p);
    			if (detaching && p_transition) p_transition.end();
    		}
    	};

    	dispatch_dev("SvelteRegisterBlock", {
    		block,
    		id: create_if_block_1$2.name,
    		type: "if",
    		source: "(405:4) {#if invalidBucketNameMessage}",
    		ctx
    	});

    	return block;
    }

    // (373:3) <PanelRow class="body flex-column">
    function create_default_slot_2$2(ctx) {
    	let div2;
    	let div0;
    	let label0;
    	let t0_value = /*$strings*/ ctx[14].bucket_name + "";
    	let t0;
    	let t1;
    	let input;
    	let input_placeholder_value;
    	let t2;
    	let div1;
    	let label1;
    	let t3_value = /*$strings*/ ctx[14].region + "";
    	let t3;
    	let t4;
    	let definedinwpconfig;
    	let t5;
    	let select;
    	let t6;
    	let if_block_anchor;
    	let current;
    	let mounted;
    	let dispose;

    	definedinwpconfig = new DefinedInWPConfig({
    			props: { defined: /*newRegionDefined*/ ctx[3] },
    			$$inline: true
    		});

    	let each_value = Object.entries(/*$storage_provider*/ ctx[13].regions);
    	validate_each_argument(each_value);
    	let each_blocks = [];

    	for (let i = 0; i < each_value.length; i += 1) {
    		each_blocks[i] = create_each_block$3(get_each_context$3(ctx, each_value, i));
    	}

    	let if_block = /*invalidBucketNameMessage*/ ctx[10] && create_if_block_1$2(ctx);

    	const block = {
    		c: function create() {
    			div2 = element("div");
    			div0 = element("div");
    			label0 = element("label");
    			t0 = text(t0_value);
    			t1 = space();
    			input = element("input");
    			t2 = space();
    			div1 = element("div");
    			label1 = element("label");
    			t3 = text(t3_value);
    			t4 = text(" ");
    			create_component(definedinwpconfig.$$.fragment);
    			t5 = space();
    			select = element("select");

    			for (let i = 0; i < each_blocks.length; i += 1) {
    				each_blocks[i].c();
    			}

    			t6 = space();
    			if (if_block) if_block.c();
    			if_block_anchor = empty();
    			attr_dev(label0, "class", "input-label");
    			attr_dev(label0, "for", "new-bucket-name");
    			add_location(label0, file$f, 375, 6, 10926);
    			attr_dev(input, "type", "text");
    			attr_dev(input, "id", "new-bucket-name");
    			attr_dev(input, "class", "bucket-name");
    			attr_dev(input, "name", "bucket");
    			attr_dev(input, "minlength", "3");
    			attr_dev(input, "placeholder", input_placeholder_value = /*$strings*/ ctx[14].enter_bucket_name_placeholder);
    			input.disabled = /*disabled*/ ctx[11];
    			toggle_class(input, "disabled", /*disabled*/ ctx[11]);
    			add_location(input, file$f, 376, 6, 11012);
    			attr_dev(div0, "class", "new-bucket-details flex-column");
    			add_location(div0, file$f, 374, 5, 10875);
    			attr_dev(label1, "class", "input-label");
    			attr_dev(label1, "for", "new-region");
    			add_location(label1, file$f, 389, 6, 11329);
    			attr_dev(select, "name", "region");
    			attr_dev(select, "id", "new-region");
    			select.disabled = /*newRegionDisabled*/ ctx[12];
    			if (/*newRegion*/ ctx[8] === void 0) add_render_callback(() => /*select_change_handler_2*/ ctx[30].call(select));
    			toggle_class(select, "disabled", /*newRegionDisabled*/ ctx[12]);
    			add_location(select, file$f, 392, 6, 11473);
    			attr_dev(div1, "class", "region flex-column");
    			add_location(div1, file$f, 388, 5, 11290);
    			attr_dev(div2, "class", "flex-row align-center row");
    			add_location(div2, file$f, 373, 4, 10830);
    		},
    		m: function mount(target, anchor) {
    			insert_dev(target, div2, anchor);
    			append_dev(div2, div0);
    			append_dev(div0, label0);
    			append_dev(label0, t0);
    			append_dev(div0, t1);
    			append_dev(div0, input);
    			set_input_value(input, /*newBucket*/ ctx[2]);
    			append_dev(div2, t2);
    			append_dev(div2, div1);
    			append_dev(div1, label1);
    			append_dev(label1, t3);
    			append_dev(label1, t4);
    			mount_component(definedinwpconfig, label1, null);
    			append_dev(div1, t5);
    			append_dev(div1, select);

    			for (let i = 0; i < each_blocks.length; i += 1) {
    				each_blocks[i].m(select, null);
    			}

    			select_option(select, /*newRegion*/ ctx[8]);
    			insert_dev(target, t6, anchor);
    			if (if_block) if_block.m(target, anchor);
    			insert_dev(target, if_block_anchor, anchor);
    			current = true;

    			if (!mounted) {
    				dispose = [
    					listen_dev(input, "input", /*input_input_handler_1*/ ctx[29]),
    					listen_dev(select, "change", /*select_change_handler_2*/ ctx[30])
    				];

    				mounted = true;
    			}
    		},
    		p: function update(ctx, dirty) {
    			if ((!current || dirty[0] & /*$strings*/ 16384) && t0_value !== (t0_value = /*$strings*/ ctx[14].bucket_name + "")) set_data_dev(t0, t0_value);

    			if (!current || dirty[0] & /*$strings*/ 16384 && input_placeholder_value !== (input_placeholder_value = /*$strings*/ ctx[14].enter_bucket_name_placeholder)) {
    				attr_dev(input, "placeholder", input_placeholder_value);
    			}

    			if (!current || dirty[0] & /*disabled*/ 2048) {
    				prop_dev(input, "disabled", /*disabled*/ ctx[11]);
    			}

    			if (dirty[0] & /*newBucket*/ 4 && input.value !== /*newBucket*/ ctx[2]) {
    				set_input_value(input, /*newBucket*/ ctx[2]);
    			}

    			if (dirty[0] & /*disabled*/ 2048) {
    				toggle_class(input, "disabled", /*disabled*/ ctx[11]);
    			}

    			if ((!current || dirty[0] & /*$strings*/ 16384) && t3_value !== (t3_value = /*$strings*/ ctx[14].region + "")) set_data_dev(t3, t3_value);
    			const definedinwpconfig_changes = {};
    			if (dirty[0] & /*newRegionDefined*/ 8) definedinwpconfig_changes.defined = /*newRegionDefined*/ ctx[3];
    			definedinwpconfig.$set(definedinwpconfig_changes);

    			if (dirty[0] & /*$storage_provider, newRegion*/ 8448) {
    				each_value = Object.entries(/*$storage_provider*/ ctx[13].regions);
    				validate_each_argument(each_value);
    				let i;

    				for (i = 0; i < each_value.length; i += 1) {
    					const child_ctx = get_each_context$3(ctx, each_value, i);

    					if (each_blocks[i]) {
    						each_blocks[i].p(child_ctx, dirty);
    					} else {
    						each_blocks[i] = create_each_block$3(child_ctx);
    						each_blocks[i].c();
    						each_blocks[i].m(select, null);
    					}
    				}

    				for (; i < each_blocks.length; i += 1) {
    					each_blocks[i].d(1);
    				}

    				each_blocks.length = each_value.length;
    			}

    			if (!current || dirty[0] & /*newRegionDisabled*/ 4096) {
    				prop_dev(select, "disabled", /*newRegionDisabled*/ ctx[12]);
    			}

    			if (dirty[0] & /*newRegion, $storage_provider*/ 8448) {
    				select_option(select, /*newRegion*/ ctx[8]);
    			}

    			if (dirty[0] & /*newRegionDisabled*/ 4096) {
    				toggle_class(select, "disabled", /*newRegionDisabled*/ ctx[12]);
    			}

    			if (/*invalidBucketNameMessage*/ ctx[10]) {
    				if (if_block) {
    					if_block.p(ctx, dirty);

    					if (dirty[0] & /*invalidBucketNameMessage*/ 1024) {
    						transition_in(if_block, 1);
    					}
    				} else {
    					if_block = create_if_block_1$2(ctx);
    					if_block.c();
    					transition_in(if_block, 1);
    					if_block.m(if_block_anchor.parentNode, if_block_anchor);
    				}
    			} else if (if_block) {
    				group_outros();

    				transition_out(if_block, 1, 1, () => {
    					if_block = null;
    				});

    				check_outros();
    			}
    		},
    		i: function intro(local) {
    			if (current) return;
    			transition_in(definedinwpconfig.$$.fragment, local);
    			transition_in(if_block);
    			current = true;
    		},
    		o: function outro(local) {
    			transition_out(definedinwpconfig.$$.fragment, local);
    			transition_out(if_block);
    			current = false;
    		},
    		d: function destroy(detaching) {
    			if (detaching) detach_dev(div2);
    			destroy_component(definedinwpconfig);
    			destroy_each(each_blocks, detaching);
    			if (detaching) detach_dev(t6);
    			if (if_block) if_block.d(detaching);
    			if (detaching) detach_dev(if_block_anchor);
    			mounted = false;
    			run_all(dispose);
    		}
    	};

    	dispatch_dev("SvelteRegisterBlock", {
    		block,
    		id: create_default_slot_2$2.name,
    		type: "slot",
    		source: "(373:3) <PanelRow class=\\\"body flex-column\\\">",
    		ctx
    	});

    	return block;
    }

    // (372:2) <Panel heading={$strings.new_bucket_title} storageProvider={$storage_provider} multi {defined}>
    function create_default_slot_1$2(ctx) {
    	let panelrow;
    	let current;

    	panelrow = new PanelRow({
    			props: {
    				class: "body flex-column",
    				$$slots: { default: [create_default_slot_2$2] },
    				$$scope: { ctx }
    			},
    			$$inline: true
    		});

    	const block = {
    		c: function create() {
    			create_component(panelrow.$$.fragment);
    		},
    		m: function mount(target, anchor) {
    			mount_component(panelrow, target, anchor);
    			current = true;
    		},
    		p: function update(ctx, dirty) {
    			const panelrow_changes = {};

    			if (dirty[0] & /*invalidBucketNameMessage, newRegionDisabled, newRegion, $storage_provider, newRegionDefined, $strings, disabled, newBucket*/ 32012 | dirty[1] & /*$$scope*/ 262144) {
    				panelrow_changes.$$scope = { dirty, ctx };
    			}

    			panelrow.$set(panelrow_changes);
    		},
    		i: function intro(local) {
    			if (current) return;
    			transition_in(panelrow.$$.fragment, local);
    			current = true;
    		},
    		o: function outro(local) {
    			transition_out(panelrow.$$.fragment, local);
    			current = false;
    		},
    		d: function destroy(detaching) {
    			destroy_component(panelrow, detaching);
    		}
    	};

    	dispatch_dev("SvelteRegisterBlock", {
    		block,
    		id: create_default_slot_1$2.name,
    		type: "slot",
    		source: "(372:2) <Panel heading={$strings.new_bucket_title} storageProvider={$storage_provider} multi {defined}>",
    		ctx
    	});

    	return block;
    }

    // (258:0) <SubPage name="bucket-settings" route="/storage/bucket">
    function create_default_slot$8(ctx) {
    	let panel;
    	let t0;
    	let t1;
    	let t2;
    	let backnextbuttonsrow;
    	let current;

    	panel = new Panel({
    			props: {
    				heading: /*$strings*/ ctx[14].bucket_source_title,
    				multi: true,
    				defined: /*defined*/ ctx[4],
    				$$slots: { default: [create_default_slot_7$1] },
    				$$scope: { ctx }
    			},
    			$$inline: true
    		});

    	let if_block0 = /*bucketSource*/ ctx[0] === "existing" && create_if_block_2$1(ctx);
    	let if_block1 = /*bucketSource*/ ctx[0] === "new" && create_if_block$5(ctx);

    	backnextbuttonsrow = new BackNextButtonsRow({
    			props: {
    				nextText: /*nextText*/ ctx[9],
    				nextDisabled: /*invalidBucketNameMessage*/ ctx[10] || /*$needs_refresh*/ ctx[6] || /*$settingsLocked*/ ctx[5],
    				nextTitle: /*invalidBucketNameMessage*/ ctx[10]
    			},
    			$$inline: true
    		});

    	backnextbuttonsrow.$on("next", /*handleNext*/ ctx[19]);

    	const block = {
    		c: function create() {
    			create_component(panel.$$.fragment);
    			t0 = space();
    			if (if_block0) if_block0.c();
    			t1 = space();
    			if (if_block1) if_block1.c();
    			t2 = space();
    			create_component(backnextbuttonsrow.$$.fragment);
    		},
    		m: function mount(target, anchor) {
    			mount_component(panel, target, anchor);
    			insert_dev(target, t0, anchor);
    			if (if_block0) if_block0.m(target, anchor);
    			insert_dev(target, t1, anchor);
    			if (if_block1) if_block1.m(target, anchor);
    			insert_dev(target, t2, anchor);
    			mount_component(backnextbuttonsrow, target, anchor);
    			current = true;
    		},
    		p: function update(ctx, dirty) {
    			const panel_changes = {};
    			if (dirty[0] & /*$strings*/ 16384) panel_changes.heading = /*$strings*/ ctx[14].bucket_source_title;
    			if (dirty[0] & /*defined*/ 16) panel_changes.defined = /*defined*/ ctx[4];

    			if (dirty[0] & /*bucketSource, disabled, $strings*/ 18433 | dirty[1] & /*$$scope*/ 262144) {
    				panel_changes.$$scope = { dirty, ctx };
    			}

    			panel.$set(panel_changes);

    			if (/*bucketSource*/ ctx[0] === "existing") {
    				if (if_block0) {
    					if_block0.p(ctx, dirty);

    					if (dirty[0] & /*bucketSource*/ 1) {
    						transition_in(if_block0, 1);
    					}
    				} else {
    					if_block0 = create_if_block_2$1(ctx);
    					if_block0.c();
    					transition_in(if_block0, 1);
    					if_block0.m(t1.parentNode, t1);
    				}
    			} else if (if_block0) {
    				group_outros();

    				transition_out(if_block0, 1, 1, () => {
    					if_block0 = null;
    				});

    				check_outros();
    			}

    			if (/*bucketSource*/ ctx[0] === "new") {
    				if (if_block1) {
    					if_block1.p(ctx, dirty);

    					if (dirty[0] & /*bucketSource*/ 1) {
    						transition_in(if_block1, 1);
    					}
    				} else {
    					if_block1 = create_if_block$5(ctx);
    					if_block1.c();
    					transition_in(if_block1, 1);
    					if_block1.m(t2.parentNode, t2);
    				}
    			} else if (if_block1) {
    				group_outros();

    				transition_out(if_block1, 1, 1, () => {
    					if_block1 = null;
    				});

    				check_outros();
    			}

    			const backnextbuttonsrow_changes = {};
    			if (dirty[0] & /*nextText*/ 512) backnextbuttonsrow_changes.nextText = /*nextText*/ ctx[9];
    			if (dirty[0] & /*invalidBucketNameMessage, $needs_refresh, $settingsLocked*/ 1120) backnextbuttonsrow_changes.nextDisabled = /*invalidBucketNameMessage*/ ctx[10] || /*$needs_refresh*/ ctx[6] || /*$settingsLocked*/ ctx[5];
    			if (dirty[0] & /*invalidBucketNameMessage*/ 1024) backnextbuttonsrow_changes.nextTitle = /*invalidBucketNameMessage*/ ctx[10];
    			backnextbuttonsrow.$set(backnextbuttonsrow_changes);
    		},
    		i: function intro(local) {
    			if (current) return;
    			transition_in(panel.$$.fragment, local);
    			transition_in(if_block0);
    			transition_in(if_block1);
    			transition_in(backnextbuttonsrow.$$.fragment, local);
    			current = true;
    		},
    		o: function outro(local) {
    			transition_out(panel.$$.fragment, local);
    			transition_out(if_block0);
    			transition_out(if_block1);
    			transition_out(backnextbuttonsrow.$$.fragment, local);
    			current = false;
    		},
    		d: function destroy(detaching) {
    			destroy_component(panel, detaching);
    			if (detaching) detach_dev(t0);
    			if (if_block0) if_block0.d(detaching);
    			if (detaching) detach_dev(t1);
    			if (if_block1) if_block1.d(detaching);
    			if (detaching) detach_dev(t2);
    			destroy_component(backnextbuttonsrow, detaching);
    		}
    	};

    	dispatch_dev("SvelteRegisterBlock", {
    		block,
    		id: create_default_slot$8.name,
    		type: "slot",
    		source: "(258:0) <SubPage name=\\\"bucket-settings\\\" route=\\\"/storage/bucket\\\">",
    		ctx
    	});

    	return block;
    }

    function create_fragment$h(ctx) {
    	let subpage;
    	let current;

    	subpage = new SubPage({
    			props: {
    				name: "bucket-settings",
    				route: "/storage/bucket",
    				$$slots: { default: [create_default_slot$8] },
    				$$scope: { ctx }
    			},
    			$$inline: true
    		});

    	const block = {
    		c: function create() {
    			create_component(subpage.$$.fragment);
    		},
    		l: function claim(nodes) {
    			throw new Error("options.hydrate only works if the component was compiled with the `hydratable: true` option");
    		},
    		m: function mount(target, anchor) {
    			mount_component(subpage, target, anchor);
    			current = true;
    		},
    		p: function update(ctx, dirty) {
    			const subpage_changes = {};

    			if (dirty[0] & /*nextText, invalidBucketNameMessage, $needs_refresh, $settingsLocked, $strings, $storage_provider, defined, newRegionDisabled, newRegion, newRegionDefined, disabled, newBucket, bucketSource, $urls, enterOrSelectExisting*/ 65407 | dirty[1] & /*$$scope*/ 262144) {
    				subpage_changes.$$scope = { dirty, ctx };
    			}

    			subpage.$set(subpage_changes);
    		},
    		i: function intro(local) {
    			if (current) return;
    			transition_in(subpage.$$.fragment, local);
    			current = true;
    		},
    		o: function outro(local) {
    			transition_out(subpage.$$.fragment, local);
    			current = false;
    		},
    		d: function destroy(detaching) {
    			destroy_component(subpage, detaching);
    		}
    	};

    	dispatch_dev("SvelteRegisterBlock", {
    		block,
    		id: create_fragment$h.name,
    		type: "component",
    		source: "",
    		ctx
    	});

    	return block;
    }

    function instance$h($$self, $$props, $$invalidate) {
    	let defined;
    	let disabled;
    	let newRegionDefined;
    	let newRegionDisabled;
    	let invalidBucketNameMessage;
    	let nextText;
    	let $storage_provider;
    	let $settings;
    	let $strings;

    	let $settingsLocked,
    		$$unsubscribe_settingsLocked = noop,
    		$$subscribe_settingsLocked = () => ($$unsubscribe_settingsLocked(), $$unsubscribe_settingsLocked = subscribe(settingsLocked, $$value => $$invalidate(5, $settingsLocked = $$value)), settingsLocked);

    	let $needs_refresh;
    	let $defined_settings;
    	let $current_settings;
    	let $urls;
    	validate_store(storage_provider, 'storage_provider');
    	component_subscribe($$self, storage_provider, $$value => $$invalidate(13, $storage_provider = $$value));
    	validate_store(settings, 'settings');
    	component_subscribe($$self, settings, $$value => $$invalidate(32, $settings = $$value));
    	validate_store(strings, 'strings');
    	component_subscribe($$self, strings, $$value => $$invalidate(14, $strings = $$value));
    	validate_store(needs_refresh, 'needs_refresh');
    	component_subscribe($$self, needs_refresh, $$value => $$invalidate(6, $needs_refresh = $$value));
    	validate_store(defined_settings, 'defined_settings');
    	component_subscribe($$self, defined_settings, $$value => $$invalidate(21, $defined_settings = $$value));
    	validate_store(current_settings, 'current_settings');
    	component_subscribe($$self, current_settings, $$value => $$invalidate(22, $current_settings = $$value));
    	validate_store(urls, 'urls');
    	component_subscribe($$self, urls, $$value => $$invalidate(15, $urls = $$value));
    	$$self.$$.on_destroy.push(() => $$unsubscribe_settingsLocked());
    	let { $$slots: slots = {}, $$scope } = $$props;
    	validate_slots('BucketSettingsSubPage', slots, []);
    	const dispatch = createEventDispatcher();

    	// Parent page may want to be locked.
    	let settingsLocked = writable(false);

    	validate_store(settingsLocked, 'settingsLocked');
    	$$subscribe_settingsLocked();

    	if (hasContext("settingsLocked")) {
    		$$subscribe_settingsLocked(settingsLocked = getContext("settingsLocked"));
    	}

    	// Keep track of where we were at prior to any changes made here.
    	let initialSettings = $current_settings;

    	if (hasContext("initialSettings")) {
    		initialSettings = getContext("initialSettings");
    	}

    	// As this page does not directly alter the settings store until done,
    	// we need to keep track of any changes made elsewhere and prompt
    	// the user to refresh the page.
    	let saving = false;

    	const previousSettings = { ...$current_settings };
    	const previousDefines = { ...$defined_settings };
    	let bucketSource = "existing";
    	let enterOrSelectExisting = "enter";

    	// If $defined_settings.bucket set, must use it, and disable change.
    	let newBucket = $settings.bucket;

    	// If $defined_settings.region set, must use it, and disable change.
    	let newRegion = $settings.region;

    	/**
     * Handles clicking the Existing radio button.
     */
    	function handleExisting() {
    		if (disabled) {
    			return;
    		}

    		$$invalidate(0, bucketSource = "existing");
    	}

    	/**
     * Handles clicking the New radio button.
     */
    	function handleNew() {
    		if (disabled) {
    			return;
    		}

    		$$invalidate(0, bucketSource = "new");
    	}

    	/**
     * Calls the API to get a list of existing buckets for the currently selected storage provider and region (if applicable).
     *
     * @param {string} region
     *
     * @return {Promise<*[]>}
     */
    	async function getBuckets(region) {
    		let params = {};

    		if ($storage_provider.region_required) {
    			params = { region };
    		}

    		let data = await api.get("buckets", params);

    		if (data.hasOwnProperty("buckets")) {
    			if (data.buckets.filter(bucket => bucket.Name === newBucket).length === 0) {
    				$$invalidate(2, newBucket = "");
    			}

    			return data.buckets;
    		}

    		$$invalidate(2, newBucket = "");
    		return [];
    	}

    	/**
     * Calls the API to create a new bucket with the currently entered name and selected region.
     *
     * @return {Promise<boolean>}
     */
    	async function createBucket() {
    		let data = await api.post("buckets", { bucket: newBucket, region: newRegion });

    		if (data.hasOwnProperty("saved")) {
    			return data.saved;
    		}

    		return false;
    	}

    	/**
     * Potentially returns a reason that the provided bucket name is invalid.
     *
     * @param {string} bucket
     * @param {string} source Either "existing" or "new".
     * @param {string} existingType Either "enter" or "select".
     *
     * @return {string}
     */
    	function getInvalidBucketNameMessage(bucket, source, existingType) {
    		// If there's an invalid region defined, don't even bother looking at bucket name.
    		if (newRegionDefined && (newRegion.length === 0 || !$storage_provider.regions.hasOwnProperty(newRegion))) {
    			return $strings.defined_region_invalid;
    		}

    		const bucketNamePattern = source === "new" ? /[^a-z0-9.\-]/ : /[^a-zA-Z0-9.\-_]/;
    		let message = "";

    		if (bucket.trim().length < 1) {
    			if (source === "existing" && existingType === "select") {
    				message = $strings.no_bucket_selected;
    			} else {
    				message = $strings.create_bucket_name_missing;
    			}
    		} else if (true === bucketNamePattern.test(bucket)) {
    			message = source === "new"
    			? $strings.create_bucket_invalid_chars
    			: $strings.select_bucket_invalid_chars;
    		} else if (bucket.length < 3) {
    			message = $strings.create_bucket_name_short;
    		} else if (bucket.length > 63) {
    			message = $strings.create_bucket_name_long;
    		}

    		return message;
    	}

    	/**
     * Returns text to be used on Next button.
     *
     * @param {string} source Either "existing" or "new".
     * @param {string} existingType Either "enter" or "select".
     *
     * @return {string}
     */
    	function getNextText(source, existingType) {
    		if (source === "existing" && existingType === "enter") {
    			return $strings.save_enter_bucket;
    		}

    		if (source === "existing" && existingType === "select") {
    			return $strings.save_select_bucket;
    		}

    		if (source === "new") {
    			return $strings.save_new_bucket;
    		}

    		return $strings.next;
    	}

    	/**
     * Handles a Next button click.
     *
     * @return {Promise<void>}
     */
    	async function handleNext() {
    		if (bucketSource === "new" && false === await createBucket()) {
    			scrollNotificationsIntoView();
    			return;
    		}

    		$$invalidate(20, saving = true);
    		state.pausePeriodicFetch();
    		set_store_value(settings, $settings.bucket = newBucket, $settings);
    		set_store_value(settings, $settings.region = newRegion, $settings);
    		const result = await settings.save();

    		// If something went wrong, don't move onto next step.
    		if (result.hasOwnProperty("saved") && !result.saved) {
    			settings.reset();
    			$$invalidate(20, saving = false);
    			state.resumePeriodicFetch();
    			scrollNotificationsIntoView();
    			return;
    		}

    		state.resumePeriodicFetch();
    		result.bucketSource = bucketSource;
    		result.initialSettings = initialSettings;

    		dispatch("routeEvent", {
    			event: "settings.save",
    			data: result,
    			default: "/"
    		});
    	}

    	onMount(() => {
    		// Default to first region in storage provider if not defined and not set or not valid.
    		if (!newRegionDefined && (newRegion.length === 0 || !$storage_provider.regions.hasOwnProperty(newRegion))) {
    			$$invalidate(8, newRegion = Object.keys($storage_provider.regions)[0]);
    		}
    	});

    	const writable_props = [];

    	Object_1$1.keys($$props).forEach(key => {
    		if (!~writable_props.indexOf(key) && key.slice(0, 2) !== '$$' && key !== 'slot') console.warn(`<BucketSettingsSubPage> was created with unknown prop '${key}'`);
    	});

    	function radiobutton0_selected_binding(value) {
    		enterOrSelectExisting = value;
    		$$invalidate(1, enterOrSelectExisting);
    	}

    	function radiobutton1_selected_binding(value) {
    		enterOrSelectExisting = value;
    		$$invalidate(1, enterOrSelectExisting);
    	}

    	function input_input_handler() {
    		newBucket = this.value;
    		$$invalidate(2, newBucket);
    	}

    	function select_change_handler() {
    		newRegion = select_value(this);
    		$$invalidate(8, newRegion);
    	}

    	function select_change_handler_1() {
    		newRegion = select_value(this);
    		$$invalidate(8, newRegion);
    	}

    	const click_handler = bucket => $$invalidate(2, newBucket = bucket.Name);

    	function input_input_handler_1() {
    		newBucket = this.value;
    		$$invalidate(2, newBucket);
    	}

    	function select_change_handler_2() {
    		newRegion = select_value(this);
    		$$invalidate(8, newRegion);
    	}

    	$$self.$capture_state = () => ({
    		createEventDispatcher,
    		getContext,
    		hasContext,
    		onMount,
    		writable,
    		slide,
    		pop,
    		api,
    		settings,
    		defined_settings,
    		strings,
    		storage_provider,
    		urls,
    		current_settings,
    		needs_refresh,
    		state,
    		scrollIntoView,
    		scrollNotificationsIntoView,
    		needsRefresh,
    		SubPage,
    		Panel,
    		PanelRow,
    		TabButton,
    		BackNextButtonsRow,
    		RadioButton,
    		Loading,
    		DefinedInWPConfig,
    		dispatch,
    		settingsLocked,
    		initialSettings,
    		saving,
    		previousSettings,
    		previousDefines,
    		bucketSource,
    		enterOrSelectExisting,
    		newBucket,
    		newRegion,
    		handleExisting,
    		handleNew,
    		getBuckets,
    		createBucket,
    		getInvalidBucketNameMessage,
    		getNextText,
    		handleNext,
    		newRegionDefined,
    		nextText,
    		invalidBucketNameMessage,
    		disabled,
    		newRegionDisabled,
    		defined,
    		$storage_provider,
    		$settings,
    		$strings,
    		$settingsLocked,
    		$needs_refresh,
    		$defined_settings,
    		$current_settings,
    		$urls
    	});

    	$$self.$inject_state = $$props => {
    		if ('settingsLocked' in $$props) $$subscribe_settingsLocked($$invalidate(7, settingsLocked = $$props.settingsLocked));
    		if ('initialSettings' in $$props) initialSettings = $$props.initialSettings;
    		if ('saving' in $$props) $$invalidate(20, saving = $$props.saving);
    		if ('bucketSource' in $$props) $$invalidate(0, bucketSource = $$props.bucketSource);
    		if ('enterOrSelectExisting' in $$props) $$invalidate(1, enterOrSelectExisting = $$props.enterOrSelectExisting);
    		if ('newBucket' in $$props) $$invalidate(2, newBucket = $$props.newBucket);
    		if ('newRegion' in $$props) $$invalidate(8, newRegion = $$props.newRegion);
    		if ('newRegionDefined' in $$props) $$invalidate(3, newRegionDefined = $$props.newRegionDefined);
    		if ('nextText' in $$props) $$invalidate(9, nextText = $$props.nextText);
    		if ('invalidBucketNameMessage' in $$props) $$invalidate(10, invalidBucketNameMessage = $$props.invalidBucketNameMessage);
    		if ('disabled' in $$props) $$invalidate(11, disabled = $$props.disabled);
    		if ('newRegionDisabled' in $$props) $$invalidate(12, newRegionDisabled = $$props.newRegionDisabled);
    		if ('defined' in $$props) $$invalidate(4, defined = $$props.defined);
    	};

    	if ($$props && "$$inject" in $$props) {
    		$$self.$inject_state($$props.$$inject);
    	}

    	$$self.$$.update = () => {
    		if ($$self.$$.dirty[0] & /*$needs_refresh, saving, $current_settings, $defined_settings*/ 7340096) {
    			{
    				set_store_value(needs_refresh, $needs_refresh = $needs_refresh || needsRefresh(saving, previousSettings, $current_settings, previousDefines, $defined_settings), $needs_refresh);
    			}
    		}

    		if ($$self.$$.dirty[0] & /*$defined_settings*/ 2097152) {
    			$$invalidate(4, defined = $defined_settings.includes("bucket"));
    		}

    		if ($$self.$$.dirty[0] & /*defined, $needs_refresh, $settingsLocked*/ 112) {
    			$$invalidate(11, disabled = defined || $needs_refresh || $settingsLocked);
    		}

    		if ($$self.$$.dirty[0] & /*$defined_settings*/ 2097152) {
    			$$invalidate(3, newRegionDefined = $defined_settings.includes("region"));
    		}

    		if ($$self.$$.dirty[0] & /*newRegionDefined, $needs_refresh, $settingsLocked*/ 104) {
    			$$invalidate(12, newRegionDisabled = newRegionDefined || $needs_refresh || $settingsLocked);
    		}

    		if ($$self.$$.dirty[0] & /*newBucket, bucketSource, enterOrSelectExisting*/ 7) {
    			$$invalidate(10, invalidBucketNameMessage = getInvalidBucketNameMessage(newBucket, bucketSource, enterOrSelectExisting));
    		}

    		if ($$self.$$.dirty[0] & /*bucketSource, enterOrSelectExisting*/ 3) {
    			$$invalidate(9, nextText = getNextText(bucketSource, enterOrSelectExisting));
    		}
    	};

    	return [
    		bucketSource,
    		enterOrSelectExisting,
    		newBucket,
    		newRegionDefined,
    		defined,
    		$settingsLocked,
    		$needs_refresh,
    		settingsLocked,
    		newRegion,
    		nextText,
    		invalidBucketNameMessage,
    		disabled,
    		newRegionDisabled,
    		$storage_provider,
    		$strings,
    		$urls,
    		handleExisting,
    		handleNew,
    		getBuckets,
    		handleNext,
    		saving,
    		$defined_settings,
    		$current_settings,
    		radiobutton0_selected_binding,
    		radiobutton1_selected_binding,
    		input_input_handler,
    		select_change_handler,
    		select_change_handler_1,
    		click_handler,
    		input_input_handler_1,
    		select_change_handler_2
    	];
    }

    class BucketSettingsSubPage extends SvelteComponentDev {
    	constructor(options) {
    		super(options);
    		init(this, options, instance$h, create_fragment$h, safe_not_equal, {}, null, [-1, -1]);

    		dispatch_dev("SvelteRegisterComponent", {
    			component: this,
    			tagName: "BucketSettingsSubPage",
    			options,
    			id: create_fragment$h.name
    		});
    	}
    }

    /* src/amazon-s3-and-cloudfront/ui/components/Checkbox.svelte generated by Svelte v3.49.0 */

    const file$e = "src/amazon-s3-and-cloudfront/ui/components/Checkbox.svelte";

    function create_fragment$g(ctx) {
    	let div;
    	let label;
    	let input;
    	let t;
    	let current;
    	let mounted;
    	let dispose;
    	const default_slot_template = /*#slots*/ ctx[4].default;
    	const default_slot = create_slot(default_slot_template, ctx, /*$$scope*/ ctx[3], null);

    	const block = {
    		c: function create() {
    			div = element("div");
    			label = element("label");
    			input = element("input");
    			t = space();
    			if (default_slot) default_slot.c();
    			attr_dev(input, "type", "checkbox");
    			attr_dev(input, "id", /*name*/ ctx[1]);
    			input.disabled = /*disabled*/ ctx[2];
    			add_location(input, file$e, 8, 2, 207);
    			attr_dev(label, "class", "toggle-label");
    			attr_dev(label, "for", /*name*/ ctx[1]);
    			add_location(label, file$e, 7, 1, 165);
    			attr_dev(div, "class", "checkbox");
    			toggle_class(div, "locked", /*disabled*/ ctx[2]);
    			toggle_class(div, "disabled", /*disabled*/ ctx[2]);
    			add_location(div, file$e, 6, 0, 102);
    		},
    		l: function claim(nodes) {
    			throw new Error("options.hydrate only works if the component was compiled with the `hydratable: true` option");
    		},
    		m: function mount(target, anchor) {
    			insert_dev(target, div, anchor);
    			append_dev(div, label);
    			append_dev(label, input);
    			input.checked = /*checked*/ ctx[0];
    			append_dev(label, t);

    			if (default_slot) {
    				default_slot.m(label, null);
    			}

    			current = true;

    			if (!mounted) {
    				dispose = listen_dev(input, "change", /*input_change_handler*/ ctx[5]);
    				mounted = true;
    			}
    		},
    		p: function update(ctx, [dirty]) {
    			if (!current || dirty & /*name*/ 2) {
    				attr_dev(input, "id", /*name*/ ctx[1]);
    			}

    			if (!current || dirty & /*disabled*/ 4) {
    				prop_dev(input, "disabled", /*disabled*/ ctx[2]);
    			}

    			if (dirty & /*checked*/ 1) {
    				input.checked = /*checked*/ ctx[0];
    			}

    			if (default_slot) {
    				if (default_slot.p && (!current || dirty & /*$$scope*/ 8)) {
    					update_slot_base(
    						default_slot,
    						default_slot_template,
    						ctx,
    						/*$$scope*/ ctx[3],
    						!current
    						? get_all_dirty_from_scope(/*$$scope*/ ctx[3])
    						: get_slot_changes(default_slot_template, /*$$scope*/ ctx[3], dirty, null),
    						null
    					);
    				}
    			}

    			if (!current || dirty & /*name*/ 2) {
    				attr_dev(label, "for", /*name*/ ctx[1]);
    			}

    			if (dirty & /*disabled*/ 4) {
    				toggle_class(div, "locked", /*disabled*/ ctx[2]);
    			}

    			if (dirty & /*disabled*/ 4) {
    				toggle_class(div, "disabled", /*disabled*/ ctx[2]);
    			}
    		},
    		i: function intro(local) {
    			if (current) return;
    			transition_in(default_slot, local);
    			current = true;
    		},
    		o: function outro(local) {
    			transition_out(default_slot, local);
    			current = false;
    		},
    		d: function destroy(detaching) {
    			if (detaching) detach_dev(div);
    			if (default_slot) default_slot.d(detaching);
    			mounted = false;
    			dispose();
    		}
    	};

    	dispatch_dev("SvelteRegisterBlock", {
    		block,
    		id: create_fragment$g.name,
    		type: "component",
    		source: "",
    		ctx
    	});

    	return block;
    }

    function instance$g($$self, $$props, $$invalidate) {
    	let { $$slots: slots = {}, $$scope } = $$props;
    	validate_slots('Checkbox', slots, ['default']);
    	let { name = "" } = $$props;
    	let { checked = false } = $$props;
    	let { disabled = false } = $$props;
    	const writable_props = ['name', 'checked', 'disabled'];

    	Object.keys($$props).forEach(key => {
    		if (!~writable_props.indexOf(key) && key.slice(0, 2) !== '$$' && key !== 'slot') console.warn(`<Checkbox> was created with unknown prop '${key}'`);
    	});

    	function input_change_handler() {
    		checked = this.checked;
    		$$invalidate(0, checked);
    	}

    	$$self.$$set = $$props => {
    		if ('name' in $$props) $$invalidate(1, name = $$props.name);
    		if ('checked' in $$props) $$invalidate(0, checked = $$props.checked);
    		if ('disabled' in $$props) $$invalidate(2, disabled = $$props.disabled);
    		if ('$$scope' in $$props) $$invalidate(3, $$scope = $$props.$$scope);
    	};

    	$$self.$capture_state = () => ({ name, checked, disabled });

    	$$self.$inject_state = $$props => {
    		if ('name' in $$props) $$invalidate(1, name = $$props.name);
    		if ('checked' in $$props) $$invalidate(0, checked = $$props.checked);
    		if ('disabled' in $$props) $$invalidate(2, disabled = $$props.disabled);
    	};

    	if ($$props && "$$inject" in $$props) {
    		$$self.$inject_state($$props.$$inject);
    	}

    	return [checked, name, disabled, $$scope, slots, input_change_handler];
    }

    class Checkbox extends SvelteComponentDev {
    	constructor(options) {
    		super(options);
    		init(this, options, instance$g, create_fragment$g, safe_not_equal, { name: 1, checked: 0, disabled: 2 });

    		dispatch_dev("SvelteRegisterComponent", {
    			component: this,
    			tagName: "Checkbox",
    			options,
    			id: create_fragment$g.name
    		});
    	}

    	get name() {
    		throw new Error("<Checkbox>: Props cannot be read directly from the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
    	}

    	set name(value) {
    		throw new Error("<Checkbox>: Props cannot be set directly on the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
    	}

    	get checked() {
    		throw new Error("<Checkbox>: Props cannot be read directly from the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
    	}

    	set checked(value) {
    		throw new Error("<Checkbox>: Props cannot be set directly on the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
    	}

    	get disabled() {
    		throw new Error("<Checkbox>: Props cannot be read directly from the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
    	}

    	set disabled(value) {
    		throw new Error("<Checkbox>: Props cannot be set directly on the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
    	}
    }

    /* src/amazon-s3-and-cloudfront/ui/components/SecuritySubPage.svelte generated by Svelte v3.49.0 */
    const file$d = "src/amazon-s3-and-cloudfront/ui/components/SecuritySubPage.svelte";

    // (225:3) {:else}
    function create_else_block_1(ctx) {
    	let p0;
    	let raw0_value = /*$strings*/ ctx[12].block_public_access_disabled_sub + "";
    	let t0;
    	let p1;
    	let html_tag;
    	let raw1_value = /*$delivery_provider*/ ctx[7].block_public_access_disabled_unsupported_desc + "";
    	let t1;
    	let html_tag_1;
    	let raw2_value = /*$storage_provider*/ ctx[13].block_public_access_disabled_unsupported_desc + "";

    	const block = {
    		c: function create() {
    			p0 = element("p");
    			t0 = space();
    			p1 = element("p");
    			html_tag = new HtmlTag(false);
    			t1 = space();
    			html_tag_1 = new HtmlTag(false);
    			add_location(p0, file$d, 225, 4, 7123);
    			html_tag.a = t1;
    			html_tag_1.a = null;
    			add_location(p1, file$d, 226, 4, 7184);
    		},
    		m: function mount(target, anchor) {
    			insert_dev(target, p0, anchor);
    			p0.innerHTML = raw0_value;
    			insert_dev(target, t0, anchor);
    			insert_dev(target, p1, anchor);
    			html_tag.m(raw1_value, p1);
    			append_dev(p1, t1);
    			html_tag_1.m(raw2_value, p1);
    		},
    		p: function update(ctx, dirty) {
    			if (dirty & /*$strings*/ 4096 && raw0_value !== (raw0_value = /*$strings*/ ctx[12].block_public_access_disabled_sub + "")) p0.innerHTML = raw0_value;			if (dirty & /*$delivery_provider*/ 128 && raw1_value !== (raw1_value = /*$delivery_provider*/ ctx[7].block_public_access_disabled_unsupported_desc + "")) html_tag.p(raw1_value);
    			if (dirty & /*$storage_provider*/ 8192 && raw2_value !== (raw2_value = /*$storage_provider*/ ctx[13].block_public_access_disabled_unsupported_desc + "")) html_tag_1.p(raw2_value);
    		},
    		d: function destroy(detaching) {
    			if (detaching) detach_dev(p0);
    			if (detaching) detach_dev(t0);
    			if (detaching) detach_dev(p1);
    		}
    	};

    	dispatch_dev("SvelteRegisterBlock", {
    		block,
    		id: create_else_block_1.name,
    		type: "else",
    		source: "(225:3) {:else}",
    		ctx
    	});

    	return block;
    }

    // (222:109) 
    function create_if_block_9(ctx) {
    	let p0;
    	let raw0_value = /*$strings*/ ctx[12].block_public_access_disabled_sub + "";
    	let t0;
    	let p1;
    	let html_tag;
    	let raw1_value = /*$delivery_provider*/ ctx[7].block_public_access_disabled_supported_desc + "";
    	let t1;
    	let html_tag_1;
    	let raw2_value = /*$storage_provider*/ ctx[13].block_public_access_disabled_supported_desc + "";

    	const block = {
    		c: function create() {
    			p0 = element("p");
    			t0 = space();
    			p1 = element("p");
    			html_tag = new HtmlTag(false);
    			t1 = space();
    			html_tag_1 = new HtmlTag(false);
    			add_location(p0, file$d, 222, 4, 6899);
    			html_tag.a = t1;
    			html_tag_1.a = null;
    			add_location(p1, file$d, 223, 4, 6960);
    		},
    		m: function mount(target, anchor) {
    			insert_dev(target, p0, anchor);
    			p0.innerHTML = raw0_value;
    			insert_dev(target, t0, anchor);
    			insert_dev(target, p1, anchor);
    			html_tag.m(raw1_value, p1);
    			append_dev(p1, t1);
    			html_tag_1.m(raw2_value, p1);
    		},
    		p: function update(ctx, dirty) {
    			if (dirty & /*$strings*/ 4096 && raw0_value !== (raw0_value = /*$strings*/ ctx[12].block_public_access_disabled_sub + "")) p0.innerHTML = raw0_value;			if (dirty & /*$delivery_provider*/ 128 && raw1_value !== (raw1_value = /*$delivery_provider*/ ctx[7].block_public_access_disabled_supported_desc + "")) html_tag.p(raw1_value);
    			if (dirty & /*$storage_provider*/ 8192 && raw2_value !== (raw2_value = /*$storage_provider*/ ctx[13].block_public_access_disabled_supported_desc + "")) html_tag_1.p(raw2_value);
    		},
    		d: function destroy(detaching) {
    			if (detaching) detach_dev(p0);
    			if (detaching) detach_dev(t0);
    			if (detaching) detach_dev(p1);
    		}
    	};

    	dispatch_dev("SvelteRegisterBlock", {
    		block,
    		id: create_if_block_9.name,
    		type: "if",
    		source: "(222:109) ",
    		ctx
    	});

    	return block;
    }

    // (219:109) 
    function create_if_block_8(ctx) {
    	let p0;
    	let raw0_value = /*$strings*/ ctx[12].block_public_access_enabled_sub + "";
    	let t0;
    	let p1;
    	let html_tag;
    	let raw1_value = /*$delivery_provider*/ ctx[7].block_public_access_enabled_unsupported_desc + "";
    	let t1;
    	let html_tag_1;
    	let raw2_value = /*$storage_provider*/ ctx[13].block_public_access_enabled_unsupported_desc + "";

    	const block = {
    		c: function create() {
    			p0 = element("p");
    			t0 = space();
    			p1 = element("p");
    			html_tag = new HtmlTag(false);
    			t1 = space();
    			html_tag_1 = new HtmlTag(false);
    			add_location(p0, file$d, 219, 4, 6575);
    			html_tag.a = t1;
    			html_tag_1.a = null;
    			add_location(p1, file$d, 220, 4, 6635);
    		},
    		m: function mount(target, anchor) {
    			insert_dev(target, p0, anchor);
    			p0.innerHTML = raw0_value;
    			insert_dev(target, t0, anchor);
    			insert_dev(target, p1, anchor);
    			html_tag.m(raw1_value, p1);
    			append_dev(p1, t1);
    			html_tag_1.m(raw2_value, p1);
    		},
    		p: function update(ctx, dirty) {
    			if (dirty & /*$strings*/ 4096 && raw0_value !== (raw0_value = /*$strings*/ ctx[12].block_public_access_enabled_sub + "")) p0.innerHTML = raw0_value;			if (dirty & /*$delivery_provider*/ 128 && raw1_value !== (raw1_value = /*$delivery_provider*/ ctx[7].block_public_access_enabled_unsupported_desc + "")) html_tag.p(raw1_value);
    			if (dirty & /*$storage_provider*/ 8192 && raw2_value !== (raw2_value = /*$storage_provider*/ ctx[13].block_public_access_enabled_unsupported_desc + "")) html_tag_1.p(raw2_value);
    		},
    		d: function destroy(detaching) {
    			if (detaching) detach_dev(p0);
    			if (detaching) detach_dev(t0);
    			if (detaching) detach_dev(p1);
    		}
    	};

    	dispatch_dev("SvelteRegisterBlock", {
    		block,
    		id: create_if_block_8.name,
    		type: "if",
    		source: "(219:109) ",
    		ctx
    	});

    	return block;
    }

    // (216:108) 
    function create_if_block_7(ctx) {
    	let p0;
    	let raw0_value = /*$strings*/ ctx[12].block_public_access_enabled_sub + "";
    	let t0;
    	let p1;
    	let html_tag;
    	let raw1_value = /*$delivery_provider*/ ctx[7].block_public_access_enabled_supported_desc + "";
    	let t1;
    	let html_tag_1;
    	let raw2_value = /*$storage_provider*/ ctx[13].block_public_access_enabled_supported_desc + "";

    	const block = {
    		c: function create() {
    			p0 = element("p");
    			t0 = space();
    			p1 = element("p");
    			html_tag = new HtmlTag(false);
    			t1 = space();
    			html_tag_1 = new HtmlTag(false);
    			add_location(p0, file$d, 216, 4, 6255);
    			html_tag.a = t1;
    			html_tag_1.a = null;
    			add_location(p1, file$d, 217, 4, 6315);
    		},
    		m: function mount(target, anchor) {
    			insert_dev(target, p0, anchor);
    			p0.innerHTML = raw0_value;
    			insert_dev(target, t0, anchor);
    			insert_dev(target, p1, anchor);
    			html_tag.m(raw1_value, p1);
    			append_dev(p1, t1);
    			html_tag_1.m(raw2_value, p1);
    		},
    		p: function update(ctx, dirty) {
    			if (dirty & /*$strings*/ 4096 && raw0_value !== (raw0_value = /*$strings*/ ctx[12].block_public_access_enabled_sub + "")) p0.innerHTML = raw0_value;			if (dirty & /*$delivery_provider*/ 128 && raw1_value !== (raw1_value = /*$delivery_provider*/ ctx[7].block_public_access_enabled_supported_desc + "")) html_tag.p(raw1_value);
    			if (dirty & /*$storage_provider*/ 8192 && raw2_value !== (raw2_value = /*$storage_provider*/ ctx[13].block_public_access_enabled_supported_desc + "")) html_tag_1.p(raw2_value);
    		},
    		d: function destroy(detaching) {
    			if (detaching) detach_dev(p0);
    			if (detaching) detach_dev(t0);
    			if (detaching) detach_dev(p1);
    		}
    	};

    	dispatch_dev("SvelteRegisterBlock", {
    		block,
    		id: create_if_block_7.name,
    		type: "if",
    		source: "(216:108) ",
    		ctx
    	});

    	return block;
    }

    // (213:3) {#if initialSetup && $current_settings[ "block-public-access" ] && !$delivery_provider.block_public_access_supported}
    function create_if_block_6(ctx) {
    	let p0;
    	let raw0_value = /*$strings*/ ctx[12].block_public_access_enabled_setup_sub + "";
    	let t0;
    	let p1;
    	let html_tag;
    	let raw1_value = /*$delivery_provider*/ ctx[7].block_public_access_enabled_unsupported_setup_desc + "";
    	let t1;
    	let html_tag_1;
    	let raw2_value = /*$storage_provider*/ ctx[13].block_public_access_enabled_unsupported_setup_desc + "";

    	const block = {
    		c: function create() {
    			p0 = element("p");
    			t0 = space();
    			p1 = element("p");
    			html_tag = new HtmlTag(false);
    			t1 = space();
    			html_tag_1 = new HtmlTag(false);
    			add_location(p0, file$d, 213, 4, 5914);
    			html_tag.a = t1;
    			html_tag_1.a = null;
    			add_location(p1, file$d, 214, 4, 5980);
    		},
    		m: function mount(target, anchor) {
    			insert_dev(target, p0, anchor);
    			p0.innerHTML = raw0_value;
    			insert_dev(target, t0, anchor);
    			insert_dev(target, p1, anchor);
    			html_tag.m(raw1_value, p1);
    			append_dev(p1, t1);
    			html_tag_1.m(raw2_value, p1);
    		},
    		p: function update(ctx, dirty) {
    			if (dirty & /*$strings*/ 4096 && raw0_value !== (raw0_value = /*$strings*/ ctx[12].block_public_access_enabled_setup_sub + "")) p0.innerHTML = raw0_value;			if (dirty & /*$delivery_provider*/ 128 && raw1_value !== (raw1_value = /*$delivery_provider*/ ctx[7].block_public_access_enabled_unsupported_setup_desc + "")) html_tag.p(raw1_value);
    			if (dirty & /*$storage_provider*/ 8192 && raw2_value !== (raw2_value = /*$storage_provider*/ ctx[13].block_public_access_enabled_unsupported_setup_desc + "")) html_tag_1.p(raw2_value);
    		},
    		d: function destroy(detaching) {
    			if (detaching) detach_dev(p0);
    			if (detaching) detach_dev(t0);
    			if (detaching) detach_dev(p1);
    		}
    	};

    	dispatch_dev("SvelteRegisterBlock", {
    		block,
    		id: create_if_block_6.name,
    		type: "if",
    		source: "(213:3) {#if initialSetup && $current_settings[ \\\"block-public-access\\\" ] && !$delivery_provider.block_public_access_supported}",
    		ctx
    	});

    	return block;
    }

    // (212:2) <PanelRow class="body flex-column">
    function create_default_slot_8(ctx) {
    	let if_block_anchor;

    	function select_block_type(ctx, dirty) {
    		if (/*initialSetup*/ ctx[9] && /*$current_settings*/ ctx[4]["block-public-access"] && !/*$delivery_provider*/ ctx[7].block_public_access_supported) return create_if_block_6;
    		if (/*$current_settings*/ ctx[4]["block-public-access"] && /*$delivery_provider*/ ctx[7].block_public_access_supported) return create_if_block_7;
    		if (/*$current_settings*/ ctx[4]["block-public-access"] && !/*$delivery_provider*/ ctx[7].block_public_access_supported) return create_if_block_8;
    		if (!/*$current_settings*/ ctx[4]["block-public-access"] && /*$delivery_provider*/ ctx[7].block_public_access_supported) return create_if_block_9;
    		return create_else_block_1;
    	}

    	let current_block_type = select_block_type(ctx);
    	let if_block = current_block_type(ctx);

    	const block = {
    		c: function create() {
    			if_block.c();
    			if_block_anchor = empty();
    		},
    		m: function mount(target, anchor) {
    			if_block.m(target, anchor);
    			insert_dev(target, if_block_anchor, anchor);
    		},
    		p: function update(ctx, dirty) {
    			if (current_block_type === (current_block_type = select_block_type(ctx)) && if_block) {
    				if_block.p(ctx, dirty);
    			} else {
    				if_block.d(1);
    				if_block = current_block_type(ctx);

    				if (if_block) {
    					if_block.c();
    					if_block.m(if_block_anchor.parentNode, if_block_anchor);
    				}
    			}
    		},
    		d: function destroy(detaching) {
    			if_block.d(detaching);
    			if (detaching) detach_dev(if_block_anchor);
    		}
    	};

    	dispatch_dev("SvelteRegisterBlock", {
    		block,
    		id: create_default_slot_8.name,
    		type: "slot",
    		source: "(212:2) <PanelRow class=\\\"body flex-column\\\">",
    		ctx
    	});

    	return block;
    }

    // (230:2) {#if !$current_settings[ "block-public-access" ] && blockPublicAccess && $delivery_provider.block_public_access_supported}
    function create_if_block_5(ctx) {
    	let div;
    	let panelrow;
    	let div_transition;
    	let current;

    	panelrow = new PanelRow({
    			props: {
    				class: "body flex-column toggle-reveal",
    				footer: true,
    				$$slots: { default: [create_default_slot_6] },
    				$$scope: { ctx }
    			},
    			$$inline: true
    		});

    	const block = {
    		c: function create() {
    			div = element("div");
    			create_component(panelrow.$$.fragment);
    			add_location(div, file$d, 230, 3, 7487);
    		},
    		m: function mount(target, anchor) {
    			insert_dev(target, div, anchor);
    			mount_component(panelrow, div, null);
    			current = true;
    		},
    		p: function update(ctx, dirty) {
    			const panelrow_changes = {};

    			if (dirty & /*$$scope, $needs_refresh, $settingsLocked, bapaSetupConfirmed, $delivery_provider*/ 134217954) {
    				panelrow_changes.$$scope = { dirty, ctx };
    			}

    			panelrow.$set(panelrow_changes);
    		},
    		i: function intro(local) {
    			if (current) return;
    			transition_in(panelrow.$$.fragment, local);

    			if (local) {
    				add_render_callback(() => {
    					if (!div_transition) div_transition = create_bidirectional_transition(div, slide, {}, true);
    					div_transition.run(1);
    				});
    			}

    			current = true;
    		},
    		o: function outro(local) {
    			transition_out(panelrow.$$.fragment, local);

    			if (local) {
    				if (!div_transition) div_transition = create_bidirectional_transition(div, slide, {}, false);
    				div_transition.run(0);
    			}

    			current = false;
    		},
    		d: function destroy(detaching) {
    			if (detaching) detach_dev(div);
    			destroy_component(panelrow);
    			if (detaching && div_transition) div_transition.end();
    		}
    	};

    	dispatch_dev("SvelteRegisterBlock", {
    		block,
    		id: create_if_block_5.name,
    		type: "if",
    		source: "(230:2) {#if !$current_settings[ \\\"block-public-access\\\" ] && blockPublicAccess && $delivery_provider.block_public_access_supported}",
    		ctx
    	});

    	return block;
    }

    // (233:5) <Checkbox name="confirm-setup" bind:checked={bapaSetupConfirmed} disabled={$needs_refresh || $settingsLocked}>
    function create_default_slot_7(ctx) {
    	let html_tag;
    	let raw_value = /*$delivery_provider*/ ctx[7].block_public_access_confirm_setup_prompt + "";
    	let html_anchor;

    	const block = {
    		c: function create() {
    			html_tag = new HtmlTag(false);
    			html_anchor = empty();
    			html_tag.a = html_anchor;
    		},
    		m: function mount(target, anchor) {
    			html_tag.m(raw_value, target, anchor);
    			insert_dev(target, html_anchor, anchor);
    		},
    		p: function update(ctx, dirty) {
    			if (dirty & /*$delivery_provider*/ 128 && raw_value !== (raw_value = /*$delivery_provider*/ ctx[7].block_public_access_confirm_setup_prompt + "")) html_tag.p(raw_value);
    		},
    		d: function destroy(detaching) {
    			if (detaching) detach_dev(html_anchor);
    			if (detaching) html_tag.d();
    		}
    	};

    	dispatch_dev("SvelteRegisterBlock", {
    		block,
    		id: create_default_slot_7.name,
    		type: "slot",
    		source: "(233:5) <Checkbox name=\\\"confirm-setup\\\" bind:checked={bapaSetupConfirmed} disabled={$needs_refresh || $settingsLocked}>",
    		ctx
    	});

    	return block;
    }

    // (232:4) <PanelRow class="body flex-column toggle-reveal" footer>
    function create_default_slot_6(ctx) {
    	let checkbox;
    	let updating_checked;
    	let current;

    	function checkbox_checked_binding(value) {
    		/*checkbox_checked_binding*/ ctx[17](value);
    	}

    	let checkbox_props = {
    		name: "confirm-setup",
    		disabled: /*$needs_refresh*/ ctx[6] || /*$settingsLocked*/ ctx[5],
    		$$slots: { default: [create_default_slot_7] },
    		$$scope: { ctx }
    	};

    	if (/*bapaSetupConfirmed*/ ctx[1] !== void 0) {
    		checkbox_props.checked = /*bapaSetupConfirmed*/ ctx[1];
    	}

    	checkbox = new Checkbox({ props: checkbox_props, $$inline: true });
    	binding_callbacks.push(() => bind(checkbox, 'checked', checkbox_checked_binding));

    	const block = {
    		c: function create() {
    			create_component(checkbox.$$.fragment);
    		},
    		m: function mount(target, anchor) {
    			mount_component(checkbox, target, anchor);
    			current = true;
    		},
    		p: function update(ctx, dirty) {
    			const checkbox_changes = {};
    			if (dirty & /*$needs_refresh, $settingsLocked*/ 96) checkbox_changes.disabled = /*$needs_refresh*/ ctx[6] || /*$settingsLocked*/ ctx[5];

    			if (dirty & /*$$scope, $delivery_provider*/ 134217856) {
    				checkbox_changes.$$scope = { dirty, ctx };
    			}

    			if (!updating_checked && dirty & /*bapaSetupConfirmed*/ 2) {
    				updating_checked = true;
    				checkbox_changes.checked = /*bapaSetupConfirmed*/ ctx[1];
    				add_flush_callback(() => updating_checked = false);
    			}

    			checkbox.$set(checkbox_changes);
    		},
    		i: function intro(local) {
    			if (current) return;
    			transition_in(checkbox.$$.fragment, local);
    			current = true;
    		},
    		o: function outro(local) {
    			transition_out(checkbox.$$.fragment, local);
    			current = false;
    		},
    		d: function destroy(detaching) {
    			destroy_component(checkbox, detaching);
    		}
    	};

    	dispatch_dev("SvelteRegisterBlock", {
    		block,
    		id: create_default_slot_6.name,
    		type: "slot",
    		source: "(232:4) <PanelRow class=\\\"body flex-column toggle-reveal\\\" footer>",
    		ctx
    	});

    	return block;
    }

    // (204:1) <Panel   class="toggle-header"   heading={$strings.block_public_access_title}   toggleName="block-public-access"   bind:toggle={blockPublicAccess}   helpKey="block-public-access"   multi  >
    function create_default_slot_5(ctx) {
    	let panelrow;
    	let t;
    	let if_block_anchor;
    	let current;

    	panelrow = new PanelRow({
    			props: {
    				class: "body flex-column",
    				$$slots: { default: [create_default_slot_8] },
    				$$scope: { ctx }
    			},
    			$$inline: true
    		});

    	let if_block = !/*$current_settings*/ ctx[4]["block-public-access"] && /*blockPublicAccess*/ ctx[0] && /*$delivery_provider*/ ctx[7].block_public_access_supported && create_if_block_5(ctx);

    	const block = {
    		c: function create() {
    			create_component(panelrow.$$.fragment);
    			t = space();
    			if (if_block) if_block.c();
    			if_block_anchor = empty();
    		},
    		m: function mount(target, anchor) {
    			mount_component(panelrow, target, anchor);
    			insert_dev(target, t, anchor);
    			if (if_block) if_block.m(target, anchor);
    			insert_dev(target, if_block_anchor, anchor);
    			current = true;
    		},
    		p: function update(ctx, dirty) {
    			const panelrow_changes = {};

    			if (dirty & /*$$scope, $storage_provider, $delivery_provider, $strings, initialSetup, $current_settings*/ 134230672) {
    				panelrow_changes.$$scope = { dirty, ctx };
    			}

    			panelrow.$set(panelrow_changes);

    			if (!/*$current_settings*/ ctx[4]["block-public-access"] && /*blockPublicAccess*/ ctx[0] && /*$delivery_provider*/ ctx[7].block_public_access_supported) {
    				if (if_block) {
    					if_block.p(ctx, dirty);

    					if (dirty & /*$current_settings, blockPublicAccess, $delivery_provider*/ 145) {
    						transition_in(if_block, 1);
    					}
    				} else {
    					if_block = create_if_block_5(ctx);
    					if_block.c();
    					transition_in(if_block, 1);
    					if_block.m(if_block_anchor.parentNode, if_block_anchor);
    				}
    			} else if (if_block) {
    				group_outros();

    				transition_out(if_block, 1, 1, () => {
    					if_block = null;
    				});

    				check_outros();
    			}
    		},
    		i: function intro(local) {
    			if (current) return;
    			transition_in(panelrow.$$.fragment, local);
    			transition_in(if_block);
    			current = true;
    		},
    		o: function outro(local) {
    			transition_out(panelrow.$$.fragment, local);
    			transition_out(if_block);
    			current = false;
    		},
    		d: function destroy(detaching) {
    			destroy_component(panelrow, detaching);
    			if (detaching) detach_dev(t);
    			if (if_block) if_block.d(detaching);
    			if (detaching) detach_dev(if_block_anchor);
    		}
    	};

    	dispatch_dev("SvelteRegisterBlock", {
    		block,
    		id: create_default_slot_5.name,
    		type: "slot",
    		source: "(204:1) <Panel   class=\\\"toggle-header\\\"   heading={$strings.block_public_access_title}   toggleName=\\\"block-public-access\\\"   bind:toggle={blockPublicAccess}   helpKey=\\\"block-public-access\\\"   multi  >",
    		ctx
    	});

    	return block;
    }

    // (260:3) {:else}
    function create_else_block(ctx) {
    	let p0;
    	let raw0_value = /*$strings*/ ctx[12].object_ownership_not_enforced_sub + "";
    	let t0;
    	let p1;
    	let html_tag;
    	let raw1_value = /*$delivery_provider*/ ctx[7].object_ownership_not_enforced_unsupported_desc + "";
    	let t1;
    	let html_tag_1;
    	let raw2_value = /*$storage_provider*/ ctx[13].object_ownership_not_enforced_unsupported_desc + "";

    	const block = {
    		c: function create() {
    			p0 = element("p");
    			t0 = space();
    			p1 = element("p");
    			html_tag = new HtmlTag(false);
    			t1 = space();
    			html_tag_1 = new HtmlTag(false);
    			add_location(p0, file$d, 260, 4, 9391);
    			html_tag.a = t1;
    			html_tag_1.a = null;
    			add_location(p1, file$d, 261, 4, 9453);
    		},
    		m: function mount(target, anchor) {
    			insert_dev(target, p0, anchor);
    			p0.innerHTML = raw0_value;
    			insert_dev(target, t0, anchor);
    			insert_dev(target, p1, anchor);
    			html_tag.m(raw1_value, p1);
    			append_dev(p1, t1);
    			html_tag_1.m(raw2_value, p1);
    		},
    		p: function update(ctx, dirty) {
    			if (dirty & /*$strings*/ 4096 && raw0_value !== (raw0_value = /*$strings*/ ctx[12].object_ownership_not_enforced_sub + "")) p0.innerHTML = raw0_value;			if (dirty & /*$delivery_provider*/ 128 && raw1_value !== (raw1_value = /*$delivery_provider*/ ctx[7].object_ownership_not_enforced_unsupported_desc + "")) html_tag.p(raw1_value);
    			if (dirty & /*$storage_provider*/ 8192 && raw2_value !== (raw2_value = /*$storage_provider*/ ctx[13].object_ownership_not_enforced_unsupported_desc + "")) html_tag_1.p(raw2_value);
    		},
    		d: function destroy(detaching) {
    			if (detaching) detach_dev(p0);
    			if (detaching) detach_dev(t0);
    			if (detaching) detach_dev(p1);
    		}
    	};

    	dispatch_dev("SvelteRegisterBlock", {
    		block,
    		id: create_else_block.name,
    		type: "else",
    		source: "(260:3) {:else}",
    		ctx
    	});

    	return block;
    }

    // (257:112) 
    function create_if_block_4(ctx) {
    	let p0;
    	let raw0_value = /*$strings*/ ctx[12].object_ownership_not_enforced_sub + "";
    	let t0;
    	let p1;
    	let html_tag;
    	let raw1_value = /*$delivery_provider*/ ctx[7].object_ownership_not_enforced_supported_desc + "";
    	let t1;
    	let html_tag_1;
    	let raw2_value = /*$storage_provider*/ ctx[13].object_ownership_not_enforced_supported_desc + "";

    	const block = {
    		c: function create() {
    			p0 = element("p");
    			t0 = space();
    			p1 = element("p");
    			html_tag = new HtmlTag(false);
    			t1 = space();
    			html_tag_1 = new HtmlTag(false);
    			add_location(p0, file$d, 257, 4, 9164);
    			html_tag.a = t1;
    			html_tag_1.a = null;
    			add_location(p1, file$d, 258, 4, 9226);
    		},
    		m: function mount(target, anchor) {
    			insert_dev(target, p0, anchor);
    			p0.innerHTML = raw0_value;
    			insert_dev(target, t0, anchor);
    			insert_dev(target, p1, anchor);
    			html_tag.m(raw1_value, p1);
    			append_dev(p1, t1);
    			html_tag_1.m(raw2_value, p1);
    		},
    		p: function update(ctx, dirty) {
    			if (dirty & /*$strings*/ 4096 && raw0_value !== (raw0_value = /*$strings*/ ctx[12].object_ownership_not_enforced_sub + "")) p0.innerHTML = raw0_value;			if (dirty & /*$delivery_provider*/ 128 && raw1_value !== (raw1_value = /*$delivery_provider*/ ctx[7].object_ownership_not_enforced_supported_desc + "")) html_tag.p(raw1_value);
    			if (dirty & /*$storage_provider*/ 8192 && raw2_value !== (raw2_value = /*$storage_provider*/ ctx[13].object_ownership_not_enforced_supported_desc + "")) html_tag_1.p(raw2_value);
    		},
    		d: function destroy(detaching) {
    			if (detaching) detach_dev(p0);
    			if (detaching) detach_dev(t0);
    			if (detaching) detach_dev(p1);
    		}
    	};

    	dispatch_dev("SvelteRegisterBlock", {
    		block,
    		id: create_if_block_4.name,
    		type: "if",
    		source: "(257:112) ",
    		ctx
    	});

    	return block;
    }

    // (254:112) 
    function create_if_block_3(ctx) {
    	let p0;
    	let raw0_value = /*$strings*/ ctx[12].object_ownership_enforced_sub + "";
    	let t0;
    	let p1;
    	let html_tag;
    	let raw1_value = /*$delivery_provider*/ ctx[7].object_ownership_enforced_unsupported_desc + "";
    	let t1;
    	let html_tag_1;
    	let raw2_value = /*$storage_provider*/ ctx[13].object_ownership_enforced_unsupported_desc + "";

    	const block = {
    		c: function create() {
    			p0 = element("p");
    			t0 = space();
    			p1 = element("p");
    			html_tag = new HtmlTag(false);
    			t1 = space();
    			html_tag_1 = new HtmlTag(false);
    			add_location(p0, file$d, 254, 4, 8843);
    			html_tag.a = t1;
    			html_tag_1.a = null;
    			add_location(p1, file$d, 255, 4, 8901);
    		},
    		m: function mount(target, anchor) {
    			insert_dev(target, p0, anchor);
    			p0.innerHTML = raw0_value;
    			insert_dev(target, t0, anchor);
    			insert_dev(target, p1, anchor);
    			html_tag.m(raw1_value, p1);
    			append_dev(p1, t1);
    			html_tag_1.m(raw2_value, p1);
    		},
    		p: function update(ctx, dirty) {
    			if (dirty & /*$strings*/ 4096 && raw0_value !== (raw0_value = /*$strings*/ ctx[12].object_ownership_enforced_sub + "")) p0.innerHTML = raw0_value;			if (dirty & /*$delivery_provider*/ 128 && raw1_value !== (raw1_value = /*$delivery_provider*/ ctx[7].object_ownership_enforced_unsupported_desc + "")) html_tag.p(raw1_value);
    			if (dirty & /*$storage_provider*/ 8192 && raw2_value !== (raw2_value = /*$storage_provider*/ ctx[13].object_ownership_enforced_unsupported_desc + "")) html_tag_1.p(raw2_value);
    		},
    		d: function destroy(detaching) {
    			if (detaching) detach_dev(p0);
    			if (detaching) detach_dev(t0);
    			if (detaching) detach_dev(p1);
    		}
    	};

    	dispatch_dev("SvelteRegisterBlock", {
    		block,
    		id: create_if_block_3.name,
    		type: "if",
    		source: "(254:112) ",
    		ctx
    	});

    	return block;
    }

    // (251:111) 
    function create_if_block_2(ctx) {
    	let p0;
    	let raw0_value = /*$strings*/ ctx[12].object_ownership_enforced_sub + "";
    	let t0;
    	let p1;
    	let html_tag;
    	let raw1_value = /*$delivery_provider*/ ctx[7].object_ownership_enforced_supported_desc + "";
    	let t1;
    	let html_tag_1;
    	let raw2_value = /*$storage_provider*/ ctx[13].object_ownership_enforced_supported_desc + "";

    	const block = {
    		c: function create() {
    			p0 = element("p");
    			t0 = space();
    			p1 = element("p");
    			html_tag = new HtmlTag(false);
    			t1 = space();
    			html_tag_1 = new HtmlTag(false);
    			add_location(p0, file$d, 251, 4, 8526);
    			html_tag.a = t1;
    			html_tag_1.a = null;
    			add_location(p1, file$d, 252, 4, 8584);
    		},
    		m: function mount(target, anchor) {
    			insert_dev(target, p0, anchor);
    			p0.innerHTML = raw0_value;
    			insert_dev(target, t0, anchor);
    			insert_dev(target, p1, anchor);
    			html_tag.m(raw1_value, p1);
    			append_dev(p1, t1);
    			html_tag_1.m(raw2_value, p1);
    		},
    		p: function update(ctx, dirty) {
    			if (dirty & /*$strings*/ 4096 && raw0_value !== (raw0_value = /*$strings*/ ctx[12].object_ownership_enforced_sub + "")) p0.innerHTML = raw0_value;			if (dirty & /*$delivery_provider*/ 128 && raw1_value !== (raw1_value = /*$delivery_provider*/ ctx[7].object_ownership_enforced_supported_desc + "")) html_tag.p(raw1_value);
    			if (dirty & /*$storage_provider*/ 8192 && raw2_value !== (raw2_value = /*$storage_provider*/ ctx[13].object_ownership_enforced_supported_desc + "")) html_tag_1.p(raw2_value);
    		},
    		d: function destroy(detaching) {
    			if (detaching) detach_dev(p0);
    			if (detaching) detach_dev(t0);
    			if (detaching) detach_dev(p1);
    		}
    	};

    	dispatch_dev("SvelteRegisterBlock", {
    		block,
    		id: create_if_block_2.name,
    		type: "if",
    		source: "(251:111) ",
    		ctx
    	});

    	return block;
    }

    // (248:3) {#if initialSetup && $current_settings[ "object-ownership-enforced" ] && !$delivery_provider.object_ownership_supported}
    function create_if_block_1$1(ctx) {
    	let p0;
    	let raw0_value = /*$strings*/ ctx[12].object_ownership_enforced_setup_sub + "";
    	let t0;
    	let p1;
    	let html_tag;
    	let raw1_value = /*$delivery_provider*/ ctx[7].object_ownership_enforced_unsupported_setup_desc + "";
    	let t1;
    	let html_tag_1;
    	let raw2_value = /*$storage_provider*/ ctx[13].object_ownership_enforced_unsupported_setup_desc + "";

    	const block = {
    		c: function create() {
    			p0 = element("p");
    			t0 = space();
    			p1 = element("p");
    			html_tag = new HtmlTag(false);
    			t1 = space();
    			html_tag_1 = new HtmlTag(false);
    			add_location(p0, file$d, 248, 4, 8188);
    			html_tag.a = t1;
    			html_tag_1.a = null;
    			add_location(p1, file$d, 249, 4, 8252);
    		},
    		m: function mount(target, anchor) {
    			insert_dev(target, p0, anchor);
    			p0.innerHTML = raw0_value;
    			insert_dev(target, t0, anchor);
    			insert_dev(target, p1, anchor);
    			html_tag.m(raw1_value, p1);
    			append_dev(p1, t1);
    			html_tag_1.m(raw2_value, p1);
    		},
    		p: function update(ctx, dirty) {
    			if (dirty & /*$strings*/ 4096 && raw0_value !== (raw0_value = /*$strings*/ ctx[12].object_ownership_enforced_setup_sub + "")) p0.innerHTML = raw0_value;			if (dirty & /*$delivery_provider*/ 128 && raw1_value !== (raw1_value = /*$delivery_provider*/ ctx[7].object_ownership_enforced_unsupported_setup_desc + "")) html_tag.p(raw1_value);
    			if (dirty & /*$storage_provider*/ 8192 && raw2_value !== (raw2_value = /*$storage_provider*/ ctx[13].object_ownership_enforced_unsupported_setup_desc + "")) html_tag_1.p(raw2_value);
    		},
    		d: function destroy(detaching) {
    			if (detaching) detach_dev(p0);
    			if (detaching) detach_dev(t0);
    			if (detaching) detach_dev(p1);
    		}
    	};

    	dispatch_dev("SvelteRegisterBlock", {
    		block,
    		id: create_if_block_1$1.name,
    		type: "if",
    		source: "(248:3) {#if initialSetup && $current_settings[ \\\"object-ownership-enforced\\\" ] && !$delivery_provider.object_ownership_supported}",
    		ctx
    	});

    	return block;
    }

    // (247:2) <PanelRow class="body flex-column">
    function create_default_slot_4$1(ctx) {
    	let if_block_anchor;

    	function select_block_type_1(ctx, dirty) {
    		if (/*initialSetup*/ ctx[9] && /*$current_settings*/ ctx[4]["object-ownership-enforced"] && !/*$delivery_provider*/ ctx[7].object_ownership_supported) return create_if_block_1$1;
    		if (/*$current_settings*/ ctx[4]["object-ownership-enforced"] && /*$delivery_provider*/ ctx[7].object_ownership_supported) return create_if_block_2;
    		if (/*$current_settings*/ ctx[4]["object-ownership-enforced"] && !/*$delivery_provider*/ ctx[7].object_ownership_supported) return create_if_block_3;
    		if (!/*$current_settings*/ ctx[4]["object-ownership-enforced"] && /*$delivery_provider*/ ctx[7].object_ownership_supported) return create_if_block_4;
    		return create_else_block;
    	}

    	let current_block_type = select_block_type_1(ctx);
    	let if_block = current_block_type(ctx);

    	const block = {
    		c: function create() {
    			if_block.c();
    			if_block_anchor = empty();
    		},
    		m: function mount(target, anchor) {
    			if_block.m(target, anchor);
    			insert_dev(target, if_block_anchor, anchor);
    		},
    		p: function update(ctx, dirty) {
    			if (current_block_type === (current_block_type = select_block_type_1(ctx)) && if_block) {
    				if_block.p(ctx, dirty);
    			} else {
    				if_block.d(1);
    				if_block = current_block_type(ctx);

    				if (if_block) {
    					if_block.c();
    					if_block.m(if_block_anchor.parentNode, if_block_anchor);
    				}
    			}
    		},
    		d: function destroy(detaching) {
    			if_block.d(detaching);
    			if (detaching) detach_dev(if_block_anchor);
    		}
    	};

    	dispatch_dev("SvelteRegisterBlock", {
    		block,
    		id: create_default_slot_4$1.name,
    		type: "slot",
    		source: "(247:2) <PanelRow class=\\\"body flex-column\\\">",
    		ctx
    	});

    	return block;
    }

    // (265:2) {#if !$current_settings[ "object-ownership-enforced" ] && objectOwnershipEnforced && $delivery_provider.object_ownership_supported}
    function create_if_block$4(ctx) {
    	let div;
    	let panelrow;
    	let div_transition;
    	let current;

    	panelrow = new PanelRow({
    			props: {
    				class: "body flex-column toggle-reveal",
    				$$slots: { default: [create_default_slot_2$1] },
    				$$scope: { ctx }
    			},
    			$$inline: true
    		});

    	const block = {
    		c: function create() {
    			div = element("div");
    			create_component(panelrow.$$.fragment);
    			add_location(div, file$d, 265, 3, 9767);
    		},
    		m: function mount(target, anchor) {
    			insert_dev(target, div, anchor);
    			mount_component(panelrow, div, null);
    			current = true;
    		},
    		p: function update(ctx, dirty) {
    			const panelrow_changes = {};

    			if (dirty & /*$$scope, $needs_refresh, $settingsLocked, ooeSetupConfirmed, $delivery_provider*/ 134217960) {
    				panelrow_changes.$$scope = { dirty, ctx };
    			}

    			panelrow.$set(panelrow_changes);
    		},
    		i: function intro(local) {
    			if (current) return;
    			transition_in(panelrow.$$.fragment, local);

    			if (local) {
    				add_render_callback(() => {
    					if (!div_transition) div_transition = create_bidirectional_transition(div, slide, {}, true);
    					div_transition.run(1);
    				});
    			}

    			current = true;
    		},
    		o: function outro(local) {
    			transition_out(panelrow.$$.fragment, local);

    			if (local) {
    				if (!div_transition) div_transition = create_bidirectional_transition(div, slide, {}, false);
    				div_transition.run(0);
    			}

    			current = false;
    		},
    		d: function destroy(detaching) {
    			if (detaching) detach_dev(div);
    			destroy_component(panelrow);
    			if (detaching && div_transition) div_transition.end();
    		}
    	};

    	dispatch_dev("SvelteRegisterBlock", {
    		block,
    		id: create_if_block$4.name,
    		type: "if",
    		source: "(265:2) {#if !$current_settings[ \\\"object-ownership-enforced\\\" ] && objectOwnershipEnforced && $delivery_provider.object_ownership_supported}",
    		ctx
    	});

    	return block;
    }

    // (268:5) <Checkbox name="confirm-setup" bind:checked={ooeSetupConfirmed} disabled={$needs_refresh || $settingsLocked}>
    function create_default_slot_3$1(ctx) {
    	let html_tag;
    	let raw_value = /*$delivery_provider*/ ctx[7].object_ownership_confirm_setup_prompt + "";
    	let html_anchor;

    	const block = {
    		c: function create() {
    			html_tag = new HtmlTag(false);
    			html_anchor = empty();
    			html_tag.a = html_anchor;
    		},
    		m: function mount(target, anchor) {
    			html_tag.m(raw_value, target, anchor);
    			insert_dev(target, html_anchor, anchor);
    		},
    		p: function update(ctx, dirty) {
    			if (dirty & /*$delivery_provider*/ 128 && raw_value !== (raw_value = /*$delivery_provider*/ ctx[7].object_ownership_confirm_setup_prompt + "")) html_tag.p(raw_value);
    		},
    		d: function destroy(detaching) {
    			if (detaching) detach_dev(html_anchor);
    			if (detaching) html_tag.d();
    		}
    	};

    	dispatch_dev("SvelteRegisterBlock", {
    		block,
    		id: create_default_slot_3$1.name,
    		type: "slot",
    		source: "(268:5) <Checkbox name=\\\"confirm-setup\\\" bind:checked={ooeSetupConfirmed} disabled={$needs_refresh || $settingsLocked}>",
    		ctx
    	});

    	return block;
    }

    // (267:4) <PanelRow class="body flex-column toggle-reveal">
    function create_default_slot_2$1(ctx) {
    	let checkbox;
    	let updating_checked;
    	let current;

    	function checkbox_checked_binding_1(value) {
    		/*checkbox_checked_binding_1*/ ctx[19](value);
    	}

    	let checkbox_props = {
    		name: "confirm-setup",
    		disabled: /*$needs_refresh*/ ctx[6] || /*$settingsLocked*/ ctx[5],
    		$$slots: { default: [create_default_slot_3$1] },
    		$$scope: { ctx }
    	};

    	if (/*ooeSetupConfirmed*/ ctx[3] !== void 0) {
    		checkbox_props.checked = /*ooeSetupConfirmed*/ ctx[3];
    	}

    	checkbox = new Checkbox({ props: checkbox_props, $$inline: true });
    	binding_callbacks.push(() => bind(checkbox, 'checked', checkbox_checked_binding_1));

    	const block = {
    		c: function create() {
    			create_component(checkbox.$$.fragment);
    		},
    		m: function mount(target, anchor) {
    			mount_component(checkbox, target, anchor);
    			current = true;
    		},
    		p: function update(ctx, dirty) {
    			const checkbox_changes = {};
    			if (dirty & /*$needs_refresh, $settingsLocked*/ 96) checkbox_changes.disabled = /*$needs_refresh*/ ctx[6] || /*$settingsLocked*/ ctx[5];

    			if (dirty & /*$$scope, $delivery_provider*/ 134217856) {
    				checkbox_changes.$$scope = { dirty, ctx };
    			}

    			if (!updating_checked && dirty & /*ooeSetupConfirmed*/ 8) {
    				updating_checked = true;
    				checkbox_changes.checked = /*ooeSetupConfirmed*/ ctx[3];
    				add_flush_callback(() => updating_checked = false);
    			}

    			checkbox.$set(checkbox_changes);
    		},
    		i: function intro(local) {
    			if (current) return;
    			transition_in(checkbox.$$.fragment, local);
    			current = true;
    		},
    		o: function outro(local) {
    			transition_out(checkbox.$$.fragment, local);
    			current = false;
    		},
    		d: function destroy(detaching) {
    			destroy_component(checkbox, detaching);
    		}
    	};

    	dispatch_dev("SvelteRegisterBlock", {
    		block,
    		id: create_default_slot_2$1.name,
    		type: "slot",
    		source: "(267:4) <PanelRow class=\\\"body flex-column toggle-reveal\\\">",
    		ctx
    	});

    	return block;
    }

    // (239:1) <Panel   class="toggle-header"   heading={$strings.object_ownership_title}   toggleName="object-ownership-enforced"   bind:toggle={objectOwnershipEnforced}   helpKey="object-ownership-enforced"   multi  >
    function create_default_slot_1$1(ctx) {
    	let panelrow;
    	let t;
    	let if_block_anchor;
    	let current;

    	panelrow = new PanelRow({
    			props: {
    				class: "body flex-column",
    				$$slots: { default: [create_default_slot_4$1] },
    				$$scope: { ctx }
    			},
    			$$inline: true
    		});

    	let if_block = !/*$current_settings*/ ctx[4]["object-ownership-enforced"] && /*objectOwnershipEnforced*/ ctx[2] && /*$delivery_provider*/ ctx[7].object_ownership_supported && create_if_block$4(ctx);

    	const block = {
    		c: function create() {
    			create_component(panelrow.$$.fragment);
    			t = space();
    			if (if_block) if_block.c();
    			if_block_anchor = empty();
    		},
    		m: function mount(target, anchor) {
    			mount_component(panelrow, target, anchor);
    			insert_dev(target, t, anchor);
    			if (if_block) if_block.m(target, anchor);
    			insert_dev(target, if_block_anchor, anchor);
    			current = true;
    		},
    		p: function update(ctx, dirty) {
    			const panelrow_changes = {};

    			if (dirty & /*$$scope, $storage_provider, $delivery_provider, $strings, initialSetup, $current_settings*/ 134230672) {
    				panelrow_changes.$$scope = { dirty, ctx };
    			}

    			panelrow.$set(panelrow_changes);

    			if (!/*$current_settings*/ ctx[4]["object-ownership-enforced"] && /*objectOwnershipEnforced*/ ctx[2] && /*$delivery_provider*/ ctx[7].object_ownership_supported) {
    				if (if_block) {
    					if_block.p(ctx, dirty);

    					if (dirty & /*$current_settings, objectOwnershipEnforced, $delivery_provider*/ 148) {
    						transition_in(if_block, 1);
    					}
    				} else {
    					if_block = create_if_block$4(ctx);
    					if_block.c();
    					transition_in(if_block, 1);
    					if_block.m(if_block_anchor.parentNode, if_block_anchor);
    				}
    			} else if (if_block) {
    				group_outros();

    				transition_out(if_block, 1, 1, () => {
    					if_block = null;
    				});

    				check_outros();
    			}
    		},
    		i: function intro(local) {
    			if (current) return;
    			transition_in(panelrow.$$.fragment, local);
    			transition_in(if_block);
    			current = true;
    		},
    		o: function outro(local) {
    			transition_out(panelrow.$$.fragment, local);
    			transition_out(if_block);
    			current = false;
    		},
    		d: function destroy(detaching) {
    			destroy_component(panelrow, detaching);
    			if (detaching) detach_dev(t);
    			if (if_block) if_block.d(detaching);
    			if (detaching) detach_dev(if_block_anchor);
    		}
    	};

    	dispatch_dev("SvelteRegisterBlock", {
    		block,
    		id: create_default_slot_1$1.name,
    		type: "slot",
    		source: "(239:1) <Panel   class=\\\"toggle-header\\\"   heading={$strings.object_ownership_title}   toggleName=\\\"object-ownership-enforced\\\"   bind:toggle={objectOwnershipEnforced}   helpKey=\\\"object-ownership-enforced\\\"   multi  >",
    		ctx
    	});

    	return block;
    }

    // (203:0) <SubPage name="bapa-settings" route="/storage/security">
    function create_default_slot$7(ctx) {
    	let panel0;
    	let updating_toggle;
    	let t0;
    	let panel1;
    	let updating_toggle_1;
    	let t1;
    	let backnextbuttonsrow;
    	let current;

    	function panel0_toggle_binding(value) {
    		/*panel0_toggle_binding*/ ctx[18](value);
    	}

    	let panel0_props = {
    		class: "toggle-header",
    		heading: /*$strings*/ ctx[12].block_public_access_title,
    		toggleName: "block-public-access",
    		helpKey: "block-public-access",
    		multi: true,
    		$$slots: { default: [create_default_slot_5] },
    		$$scope: { ctx }
    	};

    	if (/*blockPublicAccess*/ ctx[0] !== void 0) {
    		panel0_props.toggle = /*blockPublicAccess*/ ctx[0];
    	}

    	panel0 = new Panel({ props: panel0_props, $$inline: true });
    	binding_callbacks.push(() => bind(panel0, 'toggle', panel0_toggle_binding));

    	function panel1_toggle_binding(value) {
    		/*panel1_toggle_binding*/ ctx[20](value);
    	}

    	let panel1_props = {
    		class: "toggle-header",
    		heading: /*$strings*/ ctx[12].object_ownership_title,
    		toggleName: "object-ownership-enforced",
    		helpKey: "object-ownership-enforced",
    		multi: true,
    		$$slots: { default: [create_default_slot_1$1] },
    		$$scope: { ctx }
    	};

    	if (/*objectOwnershipEnforced*/ ctx[2] !== void 0) {
    		panel1_props.toggle = /*objectOwnershipEnforced*/ ctx[2];
    	}

    	panel1 = new Panel({ props: panel1_props, $$inline: true });
    	binding_callbacks.push(() => bind(panel1, 'toggle', panel1_toggle_binding));

    	backnextbuttonsrow = new BackNextButtonsRow({
    			props: {
    				nextText: /*nextText*/ ctx[11],
    				nextDisabled: /*nextDisabled*/ ctx[10]
    			},
    			$$inline: true
    		});

    	backnextbuttonsrow.$on("next", /*handleNext*/ ctx[14]);

    	const block = {
    		c: function create() {
    			create_component(panel0.$$.fragment);
    			t0 = space();
    			create_component(panel1.$$.fragment);
    			t1 = space();
    			create_component(backnextbuttonsrow.$$.fragment);
    		},
    		m: function mount(target, anchor) {
    			mount_component(panel0, target, anchor);
    			insert_dev(target, t0, anchor);
    			mount_component(panel1, target, anchor);
    			insert_dev(target, t1, anchor);
    			mount_component(backnextbuttonsrow, target, anchor);
    			current = true;
    		},
    		p: function update(ctx, dirty) {
    			const panel0_changes = {};
    			if (dirty & /*$strings*/ 4096) panel0_changes.heading = /*$strings*/ ctx[12].block_public_access_title;

    			if (dirty & /*$$scope, $needs_refresh, $settingsLocked, bapaSetupConfirmed, $delivery_provider, $current_settings, blockPublicAccess, $storage_provider, $strings, initialSetup*/ 134230771) {
    				panel0_changes.$$scope = { dirty, ctx };
    			}

    			if (!updating_toggle && dirty & /*blockPublicAccess*/ 1) {
    				updating_toggle = true;
    				panel0_changes.toggle = /*blockPublicAccess*/ ctx[0];
    				add_flush_callback(() => updating_toggle = false);
    			}

    			panel0.$set(panel0_changes);
    			const panel1_changes = {};
    			if (dirty & /*$strings*/ 4096) panel1_changes.heading = /*$strings*/ ctx[12].object_ownership_title;

    			if (dirty & /*$$scope, $needs_refresh, $settingsLocked, ooeSetupConfirmed, $delivery_provider, $current_settings, objectOwnershipEnforced, $storage_provider, $strings, initialSetup*/ 134230780) {
    				panel1_changes.$$scope = { dirty, ctx };
    			}

    			if (!updating_toggle_1 && dirty & /*objectOwnershipEnforced*/ 4) {
    				updating_toggle_1 = true;
    				panel1_changes.toggle = /*objectOwnershipEnforced*/ ctx[2];
    				add_flush_callback(() => updating_toggle_1 = false);
    			}

    			panel1.$set(panel1_changes);
    			const backnextbuttonsrow_changes = {};
    			if (dirty & /*nextText*/ 2048) backnextbuttonsrow_changes.nextText = /*nextText*/ ctx[11];
    			if (dirty & /*nextDisabled*/ 1024) backnextbuttonsrow_changes.nextDisabled = /*nextDisabled*/ ctx[10];
    			backnextbuttonsrow.$set(backnextbuttonsrow_changes);
    		},
    		i: function intro(local) {
    			if (current) return;
    			transition_in(panel0.$$.fragment, local);
    			transition_in(panel1.$$.fragment, local);
    			transition_in(backnextbuttonsrow.$$.fragment, local);
    			current = true;
    		},
    		o: function outro(local) {
    			transition_out(panel0.$$.fragment, local);
    			transition_out(panel1.$$.fragment, local);
    			transition_out(backnextbuttonsrow.$$.fragment, local);
    			current = false;
    		},
    		d: function destroy(detaching) {
    			destroy_component(panel0, detaching);
    			if (detaching) detach_dev(t0);
    			destroy_component(panel1, detaching);
    			if (detaching) detach_dev(t1);
    			destroy_component(backnextbuttonsrow, detaching);
    		}
    	};

    	dispatch_dev("SvelteRegisterBlock", {
    		block,
    		id: create_default_slot$7.name,
    		type: "slot",
    		source: "(203:0) <SubPage name=\\\"bapa-settings\\\" route=\\\"/storage/security\\\">",
    		ctx
    	});

    	return block;
    }

    function create_fragment$f(ctx) {
    	let subpage;
    	let current;

    	subpage = new SubPage({
    			props: {
    				name: "bapa-settings",
    				route: "/storage/security",
    				$$slots: { default: [create_default_slot$7] },
    				$$scope: { ctx }
    			},
    			$$inline: true
    		});

    	const block = {
    		c: function create() {
    			create_component(subpage.$$.fragment);
    		},
    		l: function claim(nodes) {
    			throw new Error("options.hydrate only works if the component was compiled with the `hydratable: true` option");
    		},
    		m: function mount(target, anchor) {
    			mount_component(subpage, target, anchor);
    			current = true;
    		},
    		p: function update(ctx, [dirty]) {
    			const subpage_changes = {};

    			if (dirty & /*$$scope, nextText, nextDisabled, $strings, objectOwnershipEnforced, $needs_refresh, $settingsLocked, ooeSetupConfirmed, $delivery_provider, $current_settings, $storage_provider, initialSetup, blockPublicAccess, bapaSetupConfirmed*/ 134233855) {
    				subpage_changes.$$scope = { dirty, ctx };
    			}

    			subpage.$set(subpage_changes);
    		},
    		i: function intro(local) {
    			if (current) return;
    			transition_in(subpage.$$.fragment, local);
    			current = true;
    		},
    		o: function outro(local) {
    			transition_out(subpage.$$.fragment, local);
    			current = false;
    		},
    		d: function destroy(detaching) {
    			destroy_component(subpage, detaching);
    		}
    	};

    	dispatch_dev("SvelteRegisterBlock", {
    		block,
    		id: create_fragment$f.name,
    		type: "component",
    		source: "",
    		ctx
    	});

    	return block;
    }

    function getNextDisabled(currentValue, newValue, supported, setupConfirmed, needsRefresh, settingsLocked) {
    	return needsRefresh || settingsLocked || !currentValue && newValue && supported && !setupConfirmed;
    }

    function instance$f($$self, $$props, $$invalidate) {
    	let nextText;
    	let nextDisabled;
    	let $settings;
    	let $current_settings;

    	let $settingsLocked,
    		$$unsubscribe_settingsLocked = noop,
    		$$subscribe_settingsLocked = () => ($$unsubscribe_settingsLocked(), $$unsubscribe_settingsLocked = subscribe(settingsLocked, $$value => $$invalidate(5, $settingsLocked = $$value)), settingsLocked);

    	let $needs_refresh;
    	let $delivery_provider;
    	let $strings;
    	let $defined_settings;
    	let $storage_provider;
    	validate_store(settings, 'settings');
    	component_subscribe($$self, settings, $$value => $$invalidate(21, $settings = $$value));
    	validate_store(current_settings, 'current_settings');
    	component_subscribe($$self, current_settings, $$value => $$invalidate(4, $current_settings = $$value));
    	validate_store(needs_refresh, 'needs_refresh');
    	component_subscribe($$self, needs_refresh, $$value => $$invalidate(6, $needs_refresh = $$value));
    	validate_store(delivery_provider, 'delivery_provider');
    	component_subscribe($$self, delivery_provider, $$value => $$invalidate(7, $delivery_provider = $$value));
    	validate_store(strings, 'strings');
    	component_subscribe($$self, strings, $$value => $$invalidate(12, $strings = $$value));
    	validate_store(defined_settings, 'defined_settings');
    	component_subscribe($$self, defined_settings, $$value => $$invalidate(16, $defined_settings = $$value));
    	validate_store(storage_provider, 'storage_provider');
    	component_subscribe($$self, storage_provider, $$value => $$invalidate(13, $storage_provider = $$value));
    	$$self.$$.on_destroy.push(() => $$unsubscribe_settingsLocked());
    	let { $$slots: slots = {}, $$scope } = $$props;
    	validate_slots('SecuritySubPage', slots, []);
    	const dispatch = createEventDispatcher();

    	// Parent page may want to be locked.
    	let settingsLocked = writable(false);

    	validate_store(settingsLocked, 'settingsLocked');
    	$$subscribe_settingsLocked();

    	if (hasContext("settingsLocked")) {
    		$$subscribe_settingsLocked(settingsLocked = getContext("settingsLocked"));
    	}

    	// As this page does not directly alter the settings store until done,
    	// we need to keep track of any changes made elsewhere and prompt
    	// the user to refresh the page.
    	let saving = false;

    	const previousSettings = { ...$current_settings };
    	const previousDefines = { ...$defined_settings };
    	let blockPublicAccess = $settings["block-public-access"];
    	let bapaSetupConfirmed = false;
    	let objectOwnershipEnforced = $settings["object-ownership-enforced"];
    	let ooeSetupConfirmed = false;

    	// During initial setup we show a slightly different page
    	// if ACLs disabled but unsupported by Delivery Provider.
    	let initialSetup = false;

    	if (hasContext("initialSetup")) {
    		initialSetup = getContext("initialSetup");
    	}

    	// If provider has changed, then still treat as initial setup.
    	if (!initialSetup && hasContext("initialSettings") && getContext("initialSettings").provider !== $current_settings.provider) {
    		initialSetup = true;
    	}

    	/**
     * Calls API to update the properties of the current bucket.
     *
     * @return {Promise<boolean|*>}
     */
    	async function updateBucketProperties() {
    		let data = await api.put("buckets", {
    			bucket: $settings.bucket,
    			blockPublicAccess,
    			objectOwnershipEnforced
    		});

    		if (data.hasOwnProperty("saved")) {
    			return data.saved;
    		}

    		return false;
    	}

    	/**
     * Returns text to be displayed on Next button.
     *
     * @param {boolean} bapaCurrent
     * @param {boolean} bapaNew
     * @param {boolean} ooeCurrent
     * @param {boolean} ooeNew
     * @param {boolean} needsRefresh
     * @param {boolean} settingsLocked
     *
     * @return {string}
     */
    	function getNextText(bapaCurrent, bapaNew, ooeCurrent, ooeNew, needsRefresh, settingsLocked) {
    		if (needsRefresh || settingsLocked) {
    			return $strings.settings_locked;
    		}

    		if (bapaCurrent !== bapaNew || ooeCurrent !== ooeNew) {
    			return $strings.update_bucket_security;
    		}

    		return $strings.keep_bucket_security;
    	}

    	/**
     * Handles a Next button click.
     *
     * @return {Promise<void>}
     */
    	async function handleNext() {
    		if (blockPublicAccess === $current_settings["block-public-access"] && objectOwnershipEnforced === $current_settings["object-ownership-enforced"]) {
    			dispatch("routeEvent", { event: "next", default: "/" });
    			return;
    		}

    		$$invalidate(15, saving = true);
    		state.pausePeriodicFetch();
    		const result = await updateBucketProperties();

    		// Regardless of whether update succeeded or not, make sure settings are up-to-date.
    		await settings.fetch();

    		if (false === result) {
    			$$invalidate(15, saving = false);
    			state.resumePeriodicFetch();
    			scrollNotificationsIntoView();
    			return;
    		}

    		state.resumePeriodicFetch();

    		// Block All Public Access changed.
    		dispatch("routeEvent", {
    			event: "bucket-security",
    			data: {
    				blockPublicAccess: $settings["block-public-access"],
    				objectOwnershipEnforced: $settings["object-ownership-enforced"]
    			},
    			default: "/"
    		});
    	}

    	const writable_props = [];

    	Object.keys($$props).forEach(key => {
    		if (!~writable_props.indexOf(key) && key.slice(0, 2) !== '$$' && key !== 'slot') console.warn(`<SecuritySubPage> was created with unknown prop '${key}'`);
    	});

    	function checkbox_checked_binding(value) {
    		bapaSetupConfirmed = value;
    		$$invalidate(1, bapaSetupConfirmed);
    	}

    	function panel0_toggle_binding(value) {
    		blockPublicAccess = value;
    		$$invalidate(0, blockPublicAccess);
    	}

    	function checkbox_checked_binding_1(value) {
    		ooeSetupConfirmed = value;
    		$$invalidate(3, ooeSetupConfirmed);
    	}

    	function panel1_toggle_binding(value) {
    		objectOwnershipEnforced = value;
    		$$invalidate(2, objectOwnershipEnforced);
    	}

    	$$self.$capture_state = () => ({
    		createEventDispatcher,
    		getContext,
    		hasContext,
    		writable,
    		slide,
    		pop,
    		api,
    		settings,
    		strings,
    		current_settings,
    		storage_provider,
    		delivery_provider,
    		needs_refresh,
    		state,
    		defined_settings,
    		scrollNotificationsIntoView,
    		needsRefresh,
    		SubPage,
    		Panel,
    		PanelRow,
    		BackNextButtonsRow,
    		Checkbox,
    		dispatch,
    		settingsLocked,
    		saving,
    		previousSettings,
    		previousDefines,
    		blockPublicAccess,
    		bapaSetupConfirmed,
    		objectOwnershipEnforced,
    		ooeSetupConfirmed,
    		initialSetup,
    		updateBucketProperties,
    		getNextText,
    		getNextDisabled,
    		handleNext,
    		nextDisabled,
    		nextText,
    		$settings,
    		$current_settings,
    		$settingsLocked,
    		$needs_refresh,
    		$delivery_provider,
    		$strings,
    		$defined_settings,
    		$storage_provider
    	});

    	$$self.$inject_state = $$props => {
    		if ('settingsLocked' in $$props) $$subscribe_settingsLocked($$invalidate(8, settingsLocked = $$props.settingsLocked));
    		if ('saving' in $$props) $$invalidate(15, saving = $$props.saving);
    		if ('blockPublicAccess' in $$props) $$invalidate(0, blockPublicAccess = $$props.blockPublicAccess);
    		if ('bapaSetupConfirmed' in $$props) $$invalidate(1, bapaSetupConfirmed = $$props.bapaSetupConfirmed);
    		if ('objectOwnershipEnforced' in $$props) $$invalidate(2, objectOwnershipEnforced = $$props.objectOwnershipEnforced);
    		if ('ooeSetupConfirmed' in $$props) $$invalidate(3, ooeSetupConfirmed = $$props.ooeSetupConfirmed);
    		if ('initialSetup' in $$props) $$invalidate(9, initialSetup = $$props.initialSetup);
    		if ('nextDisabled' in $$props) $$invalidate(10, nextDisabled = $$props.nextDisabled);
    		if ('nextText' in $$props) $$invalidate(11, nextText = $$props.nextText);
    	};

    	if ($$props && "$$inject" in $$props) {
    		$$self.$inject_state($$props.$$inject);
    	}

    	$$self.$$.update = () => {
    		if ($$self.$$.dirty & /*$needs_refresh, saving, $current_settings, $defined_settings*/ 98384) {
    			{
    				set_store_value(needs_refresh, $needs_refresh = $needs_refresh || needsRefresh(saving, previousSettings, $current_settings, previousDefines, $defined_settings), $needs_refresh);
    			}
    		}

    		if ($$self.$$.dirty & /*$current_settings, blockPublicAccess, objectOwnershipEnforced, $needs_refresh, $settingsLocked*/ 117) {
    			$$invalidate(11, nextText = getNextText($current_settings["block-public-access"], blockPublicAccess, $current_settings["object-ownership-enforced"], objectOwnershipEnforced, $needs_refresh, $settingsLocked));
    		}

    		if ($$self.$$.dirty & /*$current_settings, blockPublicAccess, $delivery_provider, bapaSetupConfirmed, $needs_refresh, $settingsLocked, objectOwnershipEnforced, ooeSetupConfirmed*/ 255) {
    			$$invalidate(10, nextDisabled = getNextDisabled($current_settings["block-public-access"], blockPublicAccess, $delivery_provider.block_public_access_supported, bapaSetupConfirmed, $needs_refresh, $settingsLocked) || getNextDisabled($current_settings["object-ownership-enforced"], objectOwnershipEnforced, $delivery_provider.object_ownership_supported, ooeSetupConfirmed, $needs_refresh, $settingsLocked));
    		}
    	};

    	return [
    		blockPublicAccess,
    		bapaSetupConfirmed,
    		objectOwnershipEnforced,
    		ooeSetupConfirmed,
    		$current_settings,
    		$settingsLocked,
    		$needs_refresh,
    		$delivery_provider,
    		settingsLocked,
    		initialSetup,
    		nextDisabled,
    		nextText,
    		$strings,
    		$storage_provider,
    		handleNext,
    		saving,
    		$defined_settings,
    		checkbox_checked_binding,
    		panel0_toggle_binding,
    		checkbox_checked_binding_1,
    		panel1_toggle_binding
    	];
    }

    class SecuritySubPage extends SvelteComponentDev {
    	constructor(options) {
    		super(options);
    		init(this, options, instance$f, create_fragment$f, safe_not_equal, {});

    		dispatch_dev("SvelteRegisterComponent", {
    			component: this,
    			tagName: "SecuritySubPage",
    			options,
    			id: create_fragment$f.name
    		});
    	}
    }

    /* src/amazon-s3-and-cloudfront/ui/components/DeliveryPage.svelte generated by Svelte v3.49.0 */

    const { Object: Object_1 } = globals;
    const file$c = "src/amazon-s3-and-cloudfront/ui/components/DeliveryPage.svelte";

    function get_each_context$2(ctx, list, i) {
    	const child_ctx = ctx.slice();
    	child_ctx[30] = list[i];
    	return child_ctx;
    }

    // (152:4) {#each supportedDeliveryProviders() as provider}
    function create_each_block$2(ctx) {
    	let div;
    	let tabbutton;
    	let t0;
    	let p0;
    	let raw0_value = /*provider*/ ctx[30].edge_server_support_desc + "";
    	let t1;
    	let p1;
    	let raw1_value = /*provider*/ ctx[30].signed_urls_support_desc + "";
    	let t2;
    	let helpbutton;
    	let t3;
    	let current;

    	function click_handler() {
    		return /*click_handler*/ ctx[18](/*provider*/ ctx[30]);
    	}

    	tabbutton = new TabButton({
    			props: {
    				active: /*provider*/ ctx[30].provider_key_name === /*deliveryProvider*/ ctx[1].provider_key_name,
    				disabled: /*disabled*/ ctx[5],
    				icon: /*provider*/ ctx[30].icon,
    				text: /*provider*/ ctx[30].default_provider_service_name
    			},
    			$$inline: true
    		});

    	tabbutton.$on("click", click_handler);

    	helpbutton = new HelpButton({
    			props: {
    				url: /*provider*/ ctx[30].provider_service_quick_start_url,
    				desc: /*$strings*/ ctx[8].view_quick_start_guide
    			},
    			$$inline: true
    		});

    	const block = {
    		c: function create() {
    			div = element("div");
    			create_component(tabbutton.$$.fragment);
    			t0 = space();
    			p0 = element("p");
    			t1 = space();
    			p1 = element("p");
    			t2 = space();
    			create_component(helpbutton.$$.fragment);
    			t3 = space();
    			attr_dev(p0, "class", "speed");
    			add_location(p0, file$c, 160, 6, 5127);
    			attr_dev(p1, "class", "private-media");
    			add_location(p1, file$c, 161, 6, 5196);
    			attr_dev(div, "class", "row");
    			add_location(div, file$c, 152, 5, 4839);
    		},
    		m: function mount(target, anchor) {
    			insert_dev(target, div, anchor);
    			mount_component(tabbutton, div, null);
    			append_dev(div, t0);
    			append_dev(div, p0);
    			p0.innerHTML = raw0_value;
    			append_dev(div, t1);
    			append_dev(div, p1);
    			p1.innerHTML = raw1_value;
    			append_dev(div, t2);
    			mount_component(helpbutton, div, null);
    			append_dev(div, t3);
    			current = true;
    		},
    		p: function update(new_ctx, dirty) {
    			ctx = new_ctx;
    			const tabbutton_changes = {};
    			if (dirty[0] & /*deliveryProvider*/ 2) tabbutton_changes.active = /*provider*/ ctx[30].provider_key_name === /*deliveryProvider*/ ctx[1].provider_key_name;
    			if (dirty[0] & /*disabled*/ 32) tabbutton_changes.disabled = /*disabled*/ ctx[5];
    			tabbutton.$set(tabbutton_changes);
    			const helpbutton_changes = {};
    			if (dirty[0] & /*$strings*/ 256) helpbutton_changes.desc = /*$strings*/ ctx[8].view_quick_start_guide;
    			helpbutton.$set(helpbutton_changes);
    		},
    		i: function intro(local) {
    			if (current) return;
    			transition_in(tabbutton.$$.fragment, local);
    			transition_in(helpbutton.$$.fragment, local);
    			current = true;
    		},
    		o: function outro(local) {
    			transition_out(tabbutton.$$.fragment, local);
    			transition_out(helpbutton.$$.fragment, local);
    			current = false;
    		},
    		d: function destroy(detaching) {
    			if (detaching) detach_dev(div);
    			destroy_component(tabbutton);
    			destroy_component(helpbutton);
    		}
    	};

    	dispatch_dev("SvelteRegisterBlock", {
    		block,
    		id: create_each_block$2.name,
    		type: "each",
    		source: "(152:4) {#each supportedDeliveryProviders() as provider}",
    		ctx
    	});

    	return block;
    }

    // (151:3) <PanelRow class="body flex-column delivery-provider-buttons">
    function create_default_slot_4(ctx) {
    	let each_1_anchor;
    	let current;
    	let each_value = /*supportedDeliveryProviders*/ ctx[9]();
    	validate_each_argument(each_value);
    	let each_blocks = [];

    	for (let i = 0; i < each_value.length; i += 1) {
    		each_blocks[i] = create_each_block$2(get_each_context$2(ctx, each_value, i));
    	}

    	const out = i => transition_out(each_blocks[i], 1, 1, () => {
    		each_blocks[i] = null;
    	});

    	const block = {
    		c: function create() {
    			for (let i = 0; i < each_blocks.length; i += 1) {
    				each_blocks[i].c();
    			}

    			each_1_anchor = empty();
    		},
    		m: function mount(target, anchor) {
    			for (let i = 0; i < each_blocks.length; i += 1) {
    				each_blocks[i].m(target, anchor);
    			}

    			insert_dev(target, each_1_anchor, anchor);
    			current = true;
    		},
    		p: function update(ctx, dirty) {
    			if (dirty[0] & /*supportedDeliveryProviders, $strings, deliveryProvider, disabled, handleChooseProvider*/ 1826) {
    				each_value = /*supportedDeliveryProviders*/ ctx[9]();
    				validate_each_argument(each_value);
    				let i;

    				for (i = 0; i < each_value.length; i += 1) {
    					const child_ctx = get_each_context$2(ctx, each_value, i);

    					if (each_blocks[i]) {
    						each_blocks[i].p(child_ctx, dirty);
    						transition_in(each_blocks[i], 1);
    					} else {
    						each_blocks[i] = create_each_block$2(child_ctx);
    						each_blocks[i].c();
    						transition_in(each_blocks[i], 1);
    						each_blocks[i].m(each_1_anchor.parentNode, each_1_anchor);
    					}
    				}

    				group_outros();

    				for (i = each_value.length; i < each_blocks.length; i += 1) {
    					out(i);
    				}

    				check_outros();
    			}
    		},
    		i: function intro(local) {
    			if (current) return;

    			for (let i = 0; i < each_value.length; i += 1) {
    				transition_in(each_blocks[i]);
    			}

    			current = true;
    		},
    		o: function outro(local) {
    			each_blocks = each_blocks.filter(Boolean);

    			for (let i = 0; i < each_blocks.length; i += 1) {
    				transition_out(each_blocks[i]);
    			}

    			current = false;
    		},
    		d: function destroy(detaching) {
    			destroy_each(each_blocks, detaching);
    			if (detaching) detach_dev(each_1_anchor);
    		}
    	};

    	dispatch_dev("SvelteRegisterBlock", {
    		block,
    		id: create_default_slot_4.name,
    		type: "slot",
    		source: "(151:3) <PanelRow class=\\\"body flex-column delivery-provider-buttons\\\">",
    		ctx
    	});

    	return block;
    }

    // (150:2) <Panel heading={$strings.select_delivery_provider_title} defined={defined} multi>
    function create_default_slot_3(ctx) {
    	let panelrow;
    	let current;

    	panelrow = new PanelRow({
    			props: {
    				class: "body flex-column delivery-provider-buttons",
    				$$slots: { default: [create_default_slot_4] },
    				$$scope: { ctx }
    			},
    			$$inline: true
    		});

    	const block = {
    		c: function create() {
    			create_component(panelrow.$$.fragment);
    		},
    		m: function mount(target, anchor) {
    			mount_component(panelrow, target, anchor);
    			current = true;
    		},
    		p: function update(ctx, dirty) {
    			const panelrow_changes = {};

    			if (dirty[0] & /*$strings, deliveryProvider, disabled*/ 290 | dirty[1] & /*$$scope*/ 4) {
    				panelrow_changes.$$scope = { dirty, ctx };
    			}

    			panelrow.$set(panelrow_changes);
    		},
    		i: function intro(local) {
    			if (current) return;
    			transition_in(panelrow.$$.fragment, local);
    			current = true;
    		},
    		o: function outro(local) {
    			transition_out(panelrow.$$.fragment, local);
    			current = false;
    		},
    		d: function destroy(detaching) {
    			destroy_component(panelrow, detaching);
    		}
    	};

    	dispatch_dev("SvelteRegisterBlock", {
    		block,
    		id: create_default_slot_3.name,
    		type: "slot",
    		source: "(150:2) <Panel heading={$strings.select_delivery_provider_title} defined={defined} multi>",
    		ctx
    	});

    	return block;
    }

    // (169:2) {#if deliveryProvider.provider_service_name_override_allowed}
    function create_if_block$3(ctx) {
    	let panel;
    	let current;

    	panel = new Panel({
    			props: {
    				heading: /*$strings*/ ctx[8].enter_other_cdn_name_title,
    				defined: /*serviceNameDefined*/ ctx[3],
    				multi: true,
    				$$slots: { default: [create_default_slot_1] },
    				$$scope: { ctx }
    			},
    			$$inline: true
    		});

    	const block = {
    		c: function create() {
    			create_component(panel.$$.fragment);
    		},
    		m: function mount(target, anchor) {
    			mount_component(panel, target, anchor);
    			current = true;
    		},
    		p: function update(ctx, dirty) {
    			const panel_changes = {};
    			if (dirty[0] & /*$strings*/ 256) panel_changes.heading = /*$strings*/ ctx[8].enter_other_cdn_name_title;
    			if (dirty[0] & /*serviceNameDefined*/ 8) panel_changes.defined = /*serviceNameDefined*/ ctx[3];

    			if (dirty[0] & /*$strings, serviceNameDisabled, serviceName*/ 388 | dirty[1] & /*$$scope*/ 4) {
    				panel_changes.$$scope = { dirty, ctx };
    			}

    			panel.$set(panel_changes);
    		},
    		i: function intro(local) {
    			if (current) return;
    			transition_in(panel.$$.fragment, local);
    			current = true;
    		},
    		o: function outro(local) {
    			transition_out(panel.$$.fragment, local);
    			current = false;
    		},
    		d: function destroy(detaching) {
    			destroy_component(panel, detaching);
    		}
    	};

    	dispatch_dev("SvelteRegisterBlock", {
    		block,
    		id: create_if_block$3.name,
    		type: "if",
    		source: "(169:2) {#if deliveryProvider.provider_service_name_override_allowed}",
    		ctx
    	});

    	return block;
    }

    // (171:4) <PanelRow class="body flex-column">
    function create_default_slot_2(ctx) {
    	let input;
    	let input_placeholder_value;
    	let mounted;
    	let dispose;

    	const block = {
    		c: function create() {
    			input = element("input");
    			attr_dev(input, "type", "text");
    			attr_dev(input, "class", "cdn-name");
    			attr_dev(input, "id", "cdn-name");
    			attr_dev(input, "name", "cdn-name");
    			attr_dev(input, "minlength", "4");
    			attr_dev(input, "placeholder", input_placeholder_value = /*$strings*/ ctx[8].enter_other_cdn_name_placeholder);
    			input.disabled = /*serviceNameDisabled*/ ctx[7];
    			add_location(input, file$c, 171, 5, 5626);
    		},
    		m: function mount(target, anchor) {
    			insert_dev(target, input, anchor);
    			set_input_value(input, /*serviceName*/ ctx[2]);

    			if (!mounted) {
    				dispose = listen_dev(input, "input", /*input_input_handler*/ ctx[19]);
    				mounted = true;
    			}
    		},
    		p: function update(ctx, dirty) {
    			if (dirty[0] & /*$strings*/ 256 && input_placeholder_value !== (input_placeholder_value = /*$strings*/ ctx[8].enter_other_cdn_name_placeholder)) {
    				attr_dev(input, "placeholder", input_placeholder_value);
    			}

    			if (dirty[0] & /*serviceNameDisabled*/ 128) {
    				prop_dev(input, "disabled", /*serviceNameDisabled*/ ctx[7]);
    			}

    			if (dirty[0] & /*serviceName*/ 4 && input.value !== /*serviceName*/ ctx[2]) {
    				set_input_value(input, /*serviceName*/ ctx[2]);
    			}
    		},
    		d: function destroy(detaching) {
    			if (detaching) detach_dev(input);
    			mounted = false;
    			dispose();
    		}
    	};

    	dispatch_dev("SvelteRegisterBlock", {
    		block,
    		id: create_default_slot_2.name,
    		type: "slot",
    		source: "(171:4) <PanelRow class=\\\"body flex-column\\\">",
    		ctx
    	});

    	return block;
    }

    // (170:3) <Panel heading={$strings.enter_other_cdn_name_title} defined={serviceNameDefined} multi>
    function create_default_slot_1(ctx) {
    	let panelrow;
    	let current;

    	panelrow = new PanelRow({
    			props: {
    				class: "body flex-column",
    				$$slots: { default: [create_default_slot_2] },
    				$$scope: { ctx }
    			},
    			$$inline: true
    		});

    	const block = {
    		c: function create() {
    			create_component(panelrow.$$.fragment);
    		},
    		m: function mount(target, anchor) {
    			mount_component(panelrow, target, anchor);
    			current = true;
    		},
    		p: function update(ctx, dirty) {
    			const panelrow_changes = {};

    			if (dirty[0] & /*$strings, serviceNameDisabled, serviceName*/ 388 | dirty[1] & /*$$scope*/ 4) {
    				panelrow_changes.$$scope = { dirty, ctx };
    			}

    			panelrow.$set(panelrow_changes);
    		},
    		i: function intro(local) {
    			if (current) return;
    			transition_in(panelrow.$$.fragment, local);
    			current = true;
    		},
    		o: function outro(local) {
    			transition_out(panelrow.$$.fragment, local);
    			current = false;
    		},
    		d: function destroy(detaching) {
    			destroy_component(panelrow, detaching);
    		}
    	};

    	dispatch_dev("SvelteRegisterBlock", {
    		block,
    		id: create_default_slot_1.name,
    		type: "slot",
    		source: "(170:3) <Panel heading={$strings.enter_other_cdn_name_title} defined={serviceNameDefined} multi>",
    		ctx
    	});

    	return block;
    }

    // (145:0) <Page {name} subpage on:routeEvent>
    function create_default_slot$6(ctx) {
    	let notifications;
    	let t0;
    	let h2;
    	let t1_value = /*$strings*/ ctx[8].delivery_title + "";
    	let t1;
    	let t2;
    	let div;
    	let panel;
    	let t3;
    	let t4;
    	let backnextbuttonsrow;
    	let current;

    	notifications = new Notifications({
    			props: { tab: /*name*/ ctx[0], tabParent: "media" },
    			$$inline: true
    		});

    	panel = new Panel({
    			props: {
    				heading: /*$strings*/ ctx[8].select_delivery_provider_title,
    				defined: /*defined*/ ctx[4],
    				multi: true,
    				$$slots: { default: [create_default_slot_3] },
    				$$scope: { ctx }
    			},
    			$$inline: true
    		});

    	let if_block = /*deliveryProvider*/ ctx[1].provider_service_name_override_allowed && create_if_block$3(ctx);

    	backnextbuttonsrow = new BackNextButtonsRow({
    			props: {
    				nextText: /*$strings*/ ctx[8].save_delivery_provider,
    				nextDisabled: /*nextDisabledMessage*/ ctx[6],
    				nextTitle: /*nextDisabledMessage*/ ctx[6]
    			},
    			$$inline: true
    		});

    	backnextbuttonsrow.$on("next", /*handleNext*/ ctx[11]);

    	const block = {
    		c: function create() {
    			create_component(notifications.$$.fragment);
    			t0 = space();
    			h2 = element("h2");
    			t1 = text(t1_value);
    			t2 = space();
    			div = element("div");
    			create_component(panel.$$.fragment);
    			t3 = space();
    			if (if_block) if_block.c();
    			t4 = space();
    			create_component(backnextbuttonsrow.$$.fragment);
    			attr_dev(h2, "class", "page-title");
    			add_location(h2, file$c, 146, 1, 4522);
    			attr_dev(div, "class", "delivery-provider-settings-page wrapper");
    			add_location(div, file$c, 148, 1, 4578);
    		},
    		m: function mount(target, anchor) {
    			mount_component(notifications, target, anchor);
    			insert_dev(target, t0, anchor);
    			insert_dev(target, h2, anchor);
    			append_dev(h2, t1);
    			insert_dev(target, t2, anchor);
    			insert_dev(target, div, anchor);
    			mount_component(panel, div, null);
    			append_dev(div, t3);
    			if (if_block) if_block.m(div, null);
    			append_dev(div, t4);
    			mount_component(backnextbuttonsrow, div, null);
    			current = true;
    		},
    		p: function update(ctx, dirty) {
    			const notifications_changes = {};
    			if (dirty[0] & /*name*/ 1) notifications_changes.tab = /*name*/ ctx[0];
    			notifications.$set(notifications_changes);
    			if ((!current || dirty[0] & /*$strings*/ 256) && t1_value !== (t1_value = /*$strings*/ ctx[8].delivery_title + "")) set_data_dev(t1, t1_value);
    			const panel_changes = {};
    			if (dirty[0] & /*$strings*/ 256) panel_changes.heading = /*$strings*/ ctx[8].select_delivery_provider_title;
    			if (dirty[0] & /*defined*/ 16) panel_changes.defined = /*defined*/ ctx[4];

    			if (dirty[0] & /*$strings, deliveryProvider, disabled*/ 290 | dirty[1] & /*$$scope*/ 4) {
    				panel_changes.$$scope = { dirty, ctx };
    			}

    			panel.$set(panel_changes);

    			if (/*deliveryProvider*/ ctx[1].provider_service_name_override_allowed) {
    				if (if_block) {
    					if_block.p(ctx, dirty);

    					if (dirty[0] & /*deliveryProvider*/ 2) {
    						transition_in(if_block, 1);
    					}
    				} else {
    					if_block = create_if_block$3(ctx);
    					if_block.c();
    					transition_in(if_block, 1);
    					if_block.m(div, t4);
    				}
    			} else if (if_block) {
    				group_outros();

    				transition_out(if_block, 1, 1, () => {
    					if_block = null;
    				});

    				check_outros();
    			}

    			const backnextbuttonsrow_changes = {};
    			if (dirty[0] & /*$strings*/ 256) backnextbuttonsrow_changes.nextText = /*$strings*/ ctx[8].save_delivery_provider;
    			if (dirty[0] & /*nextDisabledMessage*/ 64) backnextbuttonsrow_changes.nextDisabled = /*nextDisabledMessage*/ ctx[6];
    			if (dirty[0] & /*nextDisabledMessage*/ 64) backnextbuttonsrow_changes.nextTitle = /*nextDisabledMessage*/ ctx[6];
    			backnextbuttonsrow.$set(backnextbuttonsrow_changes);
    		},
    		i: function intro(local) {
    			if (current) return;
    			transition_in(notifications.$$.fragment, local);
    			transition_in(panel.$$.fragment, local);
    			transition_in(if_block);
    			transition_in(backnextbuttonsrow.$$.fragment, local);
    			current = true;
    		},
    		o: function outro(local) {
    			transition_out(notifications.$$.fragment, local);
    			transition_out(panel.$$.fragment, local);
    			transition_out(if_block);
    			transition_out(backnextbuttonsrow.$$.fragment, local);
    			current = false;
    		},
    		d: function destroy(detaching) {
    			destroy_component(notifications, detaching);
    			if (detaching) detach_dev(t0);
    			if (detaching) detach_dev(h2);
    			if (detaching) detach_dev(t2);
    			if (detaching) detach_dev(div);
    			destroy_component(panel);
    			if (if_block) if_block.d();
    			destroy_component(backnextbuttonsrow);
    		}
    	};

    	dispatch_dev("SvelteRegisterBlock", {
    		block,
    		id: create_default_slot$6.name,
    		type: "slot",
    		source: "(145:0) <Page {name} subpage on:routeEvent>",
    		ctx
    	});

    	return block;
    }

    function create_fragment$e(ctx) {
    	let page;
    	let current;

    	page = new Page({
    			props: {
    				name: /*name*/ ctx[0],
    				subpage: true,
    				$$slots: { default: [create_default_slot$6] },
    				$$scope: { ctx }
    			},
    			$$inline: true
    		});

    	page.$on("routeEvent", /*routeEvent_handler*/ ctx[20]);

    	const block = {
    		c: function create() {
    			create_component(page.$$.fragment);
    		},
    		l: function claim(nodes) {
    			throw new Error("options.hydrate only works if the component was compiled with the `hydratable: true` option");
    		},
    		m: function mount(target, anchor) {
    			mount_component(page, target, anchor);
    			current = true;
    		},
    		p: function update(ctx, dirty) {
    			const page_changes = {};
    			if (dirty[0] & /*name*/ 1) page_changes.name = /*name*/ ctx[0];

    			if (dirty[0] & /*$strings, nextDisabledMessage, serviceNameDefined, serviceNameDisabled, serviceName, deliveryProvider, defined, disabled, name*/ 511 | dirty[1] & /*$$scope*/ 4) {
    				page_changes.$$scope = { dirty, ctx };
    			}

    			page.$set(page_changes);
    		},
    		i: function intro(local) {
    			if (current) return;
    			transition_in(page.$$.fragment, local);
    			current = true;
    		},
    		o: function outro(local) {
    			transition_out(page.$$.fragment, local);
    			current = false;
    		},
    		d: function destroy(detaching) {
    			destroy_component(page, detaching);
    		}
    	};

    	dispatch_dev("SvelteRegisterBlock", {
    		block,
    		id: create_fragment$e.name,
    		type: "component",
    		source: "",
    		ctx
    	});

    	return block;
    }

    function instance$e($$self, $$props, $$invalidate) {
    	let defined;
    	let disabled;
    	let serviceNameDefined;
    	let serviceNameDisabled;
    	let nextDisabledMessage;
    	let $settings;
    	let $needs_refresh;
    	let $settingsLocked;
    	let $strings;
    	let $delivery_provider;
    	let $storage_provider;
    	let $delivery_providers;
    	let $defined_settings;
    	let $current_settings;
    	validate_store(settings, 'settings');
    	component_subscribe($$self, settings, $$value => $$invalidate(21, $settings = $$value));
    	validate_store(needs_refresh, 'needs_refresh');
    	component_subscribe($$self, needs_refresh, $$value => $$invalidate(14, $needs_refresh = $$value));
    	validate_store(settingsLocked, 'settingsLocked');
    	component_subscribe($$self, settingsLocked, $$value => $$invalidate(15, $settingsLocked = $$value));
    	validate_store(strings, 'strings');
    	component_subscribe($$self, strings, $$value => $$invalidate(8, $strings = $$value));
    	validate_store(delivery_provider, 'delivery_provider');
    	component_subscribe($$self, delivery_provider, $$value => $$invalidate(22, $delivery_provider = $$value));
    	validate_store(storage_provider, 'storage_provider');
    	component_subscribe($$self, storage_provider, $$value => $$invalidate(23, $storage_provider = $$value));
    	validate_store(delivery_providers, 'delivery_providers');
    	component_subscribe($$self, delivery_providers, $$value => $$invalidate(24, $delivery_providers = $$value));
    	validate_store(defined_settings, 'defined_settings');
    	component_subscribe($$self, defined_settings, $$value => $$invalidate(16, $defined_settings = $$value));
    	validate_store(current_settings, 'current_settings');
    	component_subscribe($$self, current_settings, $$value => $$invalidate(17, $current_settings = $$value));
    	let { $$slots: slots = {}, $$scope } = $$props;
    	validate_slots('DeliveryPage', slots, []);
    	const dispatch = createEventDispatcher();
    	let { name = "delivery-provider" } = $$props;
    	let { params = {} } = $$props;
    	const _params = params; // Stops compiler warning about unused params export;

    	// Let all child components know if settings are currently locked.
    	setContext("settingsLocked", settingsLocked);

    	// As this page does not directly alter the settings store until done,
    	// we need to keep track of any changes made elsewhere and prompt
    	// the user to refresh the page.
    	let saving = false;

    	const previousSettings = { ...$current_settings };
    	const previousDefines = { ...$defined_settings };

    	// Start with a copy of the current delivery provider.
    	let deliveryProvider = { ...$delivery_provider };

    	let serviceName = $settings["delivery-provider-service-name"];

    	/**
     * Returns an array of delivery providers that can be used with the currently configured storage provider.
     *
     * @return {array}
     */
    	function supportedDeliveryProviders() {
    		return Object.values($delivery_providers).filter(provider => provider.supported_storage_providers.length === 0 || provider.supported_storage_providers.includes($storage_provider.provider_key_name));
    	}

    	/**
     * Determines whether the Next button should be disabled or not and returns a suitable reason.
     *
     * @param {Object} provider
     * @param {string} providerName
     * @param {boolean} settingsLocked
     * @param {boolean} needsRefresh
     *
     * @return {string}
     */
    	function getNextDisabledMessage(provider, providerName, settingsLocked, needsRefresh) {
    		let message = "";

    		if (settingsLocked || needsRefresh) {
    			message = $strings.settings_locked;
    		} else if (provider.provider_service_name_override_allowed && providerName.trim().length < 1) {
    			message = $strings.no_delivery_provider_name;
    		} else if (provider.provider_service_name_override_allowed && providerName.trim().length < 4) {
    			message = $strings.delivery_provider_name_short;
    		} else if (deliveryProvider.provider_key_name === $delivery_provider.provider_key_name && providerName === $settings["delivery-provider-service-name"]) {
    			message = $strings.nothing_to_save;
    		}

    		return message;
    	}

    	/**
     * Handles choosing a different delivery provider.
     *
     * @param {Object} provider
     */
    	function handleChooseProvider(provider) {
    		if (disabled) {
    			return;
    		}

    		$$invalidate(1, deliveryProvider = provider);
    	}

    	/**
     * Handles a Next button click.
     *
     * @return {Promise<void>}
     */
    	async function handleNext() {
    		$$invalidate(13, saving = true);
    		state.pausePeriodicFetch();
    		set_store_value(settings, $settings["delivery-provider"] = deliveryProvider.provider_key_name, $settings);
    		set_store_value(settings, $settings["delivery-provider-service-name"] = serviceName, $settings);
    		const result = await settings.save();

    		// If something went wrong, don't move onto next step.
    		if (result.hasOwnProperty("saved") && !result.saved) {
    			settings.reset();
    			$$invalidate(13, saving = false);
    			state.resumePeriodicFetch();
    			scrollNotificationsIntoView();
    			return;
    		}

    		state.resumePeriodicFetch();

    		dispatch("routeEvent", {
    			event: "settings.save",
    			data: result,
    			default: "/media/delivery"
    		});
    	}

    	const writable_props = ['name', 'params'];

    	Object_1.keys($$props).forEach(key => {
    		if (!~writable_props.indexOf(key) && key.slice(0, 2) !== '$$' && key !== 'slot') console.warn(`<DeliveryPage> was created with unknown prop '${key}'`);
    	});

    	const click_handler = provider => handleChooseProvider(provider);

    	function input_input_handler() {
    		serviceName = this.value;
    		$$invalidate(2, serviceName);
    	}

    	function routeEvent_handler(event) {
    		bubble.call(this, $$self, event);
    	}

    	$$self.$$set = $$props => {
    		if ('name' in $$props) $$invalidate(0, name = $$props.name);
    		if ('params' in $$props) $$invalidate(12, params = $$props.params);
    	};

    	$$self.$capture_state = () => ({
    		createEventDispatcher,
    		setContext,
    		replace,
    		strings,
    		settings,
    		storage_provider,
    		delivery_providers,
    		delivery_provider,
    		defined_settings,
    		settingsLocked,
    		current_settings,
    		needs_refresh,
    		state,
    		scrollNotificationsIntoView,
    		needsRefresh,
    		Page,
    		Notifications,
    		Panel,
    		PanelRow,
    		TabButton,
    		BackNextButtonsRow,
    		HelpButton,
    		dispatch,
    		name,
    		params,
    		_params,
    		saving,
    		previousSettings,
    		previousDefines,
    		deliveryProvider,
    		serviceName,
    		supportedDeliveryProviders,
    		getNextDisabledMessage,
    		handleChooseProvider,
    		handleNext,
    		disabled,
    		nextDisabledMessage,
    		serviceNameDefined,
    		serviceNameDisabled,
    		defined,
    		$settings,
    		$needs_refresh,
    		$settingsLocked,
    		$strings,
    		$delivery_provider,
    		$storage_provider,
    		$delivery_providers,
    		$defined_settings,
    		$current_settings
    	});

    	$$self.$inject_state = $$props => {
    		if ('name' in $$props) $$invalidate(0, name = $$props.name);
    		if ('params' in $$props) $$invalidate(12, params = $$props.params);
    		if ('saving' in $$props) $$invalidate(13, saving = $$props.saving);
    		if ('deliveryProvider' in $$props) $$invalidate(1, deliveryProvider = $$props.deliveryProvider);
    		if ('serviceName' in $$props) $$invalidate(2, serviceName = $$props.serviceName);
    		if ('disabled' in $$props) $$invalidate(5, disabled = $$props.disabled);
    		if ('nextDisabledMessage' in $$props) $$invalidate(6, nextDisabledMessage = $$props.nextDisabledMessage);
    		if ('serviceNameDefined' in $$props) $$invalidate(3, serviceNameDefined = $$props.serviceNameDefined);
    		if ('serviceNameDisabled' in $$props) $$invalidate(7, serviceNameDisabled = $$props.serviceNameDisabled);
    		if ('defined' in $$props) $$invalidate(4, defined = $$props.defined);
    	};

    	if ($$props && "$$inject" in $$props) {
    		$$self.$inject_state($$props.$$inject);
    	}

    	$$self.$$.update = () => {
    		if ($$self.$$.dirty[0] & /*$needs_refresh, saving, $current_settings, $defined_settings*/ 221184) {
    			{
    				set_store_value(needs_refresh, $needs_refresh = $needs_refresh || needsRefresh(saving, previousSettings, $current_settings, previousDefines, $defined_settings), $needs_refresh);
    			}
    		}

    		if ($$self.$$.dirty[0] & /*$defined_settings*/ 65536) {
    			$$invalidate(4, defined = $defined_settings.includes("delivery-provider"));
    		}

    		if ($$self.$$.dirty[0] & /*defined, $settingsLocked*/ 32784) {
    			$$invalidate(5, disabled = defined || $settingsLocked);
    		}

    		if ($$self.$$.dirty[0] & /*$defined_settings*/ 65536) {
    			$$invalidate(3, serviceNameDefined = $defined_settings.includes("delivery-provider-service-name"));
    		}

    		if ($$self.$$.dirty[0] & /*serviceNameDefined, $settingsLocked*/ 32776) {
    			$$invalidate(7, serviceNameDisabled = serviceNameDefined || $settingsLocked);
    		}

    		if ($$self.$$.dirty[0] & /*deliveryProvider, serviceName, $settingsLocked, $needs_refresh*/ 49158) {
    			$$invalidate(6, nextDisabledMessage = getNextDisabledMessage(deliveryProvider, serviceName, $settingsLocked, $needs_refresh));
    		}
    	};

    	return [
    		name,
    		deliveryProvider,
    		serviceName,
    		serviceNameDefined,
    		defined,
    		disabled,
    		nextDisabledMessage,
    		serviceNameDisabled,
    		$strings,
    		supportedDeliveryProviders,
    		handleChooseProvider,
    		handleNext,
    		params,
    		saving,
    		$needs_refresh,
    		$settingsLocked,
    		$defined_settings,
    		$current_settings,
    		click_handler,
    		input_input_handler,
    		routeEvent_handler
    	];
    }

    class DeliveryPage extends SvelteComponentDev {
    	constructor(options) {
    		super(options);
    		init(this, options, instance$e, create_fragment$e, safe_not_equal, { name: 0, params: 12 }, null, [-1, -1]);

    		dispatch_dev("SvelteRegisterComponent", {
    			component: this,
    			tagName: "DeliveryPage",
    			options,
    			id: create_fragment$e.name
    		});
    	}

    	get name() {
    		throw new Error("<DeliveryPage>: Props cannot be read directly from the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
    	}

    	set name(value) {
    		throw new Error("<DeliveryPage>: Props cannot be set directly on the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
    	}

    	get params() {
    		throw new Error("<DeliveryPage>: Props cannot be read directly from the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
    	}

    	set params(value) {
    		throw new Error("<DeliveryPage>: Props cannot be set directly on the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
    	}
    }

    // Default pages, having a title means inclusion in main tabs.
    // NOTE: get() only resolves after initialization, hence arrow functions for getting titles.
    const defaultPages = [
    	{
    		position: 0,
    		name: "media-library",
    		title: () => get_store_value( strings ).media_tab_title,
    		nav: true,
    		route: "/",
    		routeMatcher: /^\/(media\/.*)*$/,
    		component: MediaPage,
    		default: true
    	},
    	{
    		position: 200,
    		name: "storage",
    		route: "/storage/*",
    		component: StoragePage
    	},
    	{
    		position: 210,
    		name: "storage-provider",
    		title: () => get_store_value( strings ).storage_provider_tab_title,
    		subNav: true,
    		route: "/storage/provider",
    		component: StorageProviderSubPage,
    		default: true,
    		events: {
    			"page.initial.settings": ( data ) => {
    				// We need Storage Provider credentials for some pages to be useful.
    				if ( data.hasOwnProperty( "location" ) && get_store_value( needs_access_keys ) && !get_store_value( is_plugin_setup ) ) {
    					for ( const prefix of ["/storage", "/media", "/delivery"] ) {
    						if ( data.location.startsWith( prefix ) ) {
    							return true;
    						}
    					}

    					return data.location === "/";
    				}

    				return false;
    			}
    		}
    	},
    	{
    		position: 220,
    		name: "bucket",
    		title: () => get_store_value( strings ).bucket_tab_title,
    		subNav: true,
    		route: "/storage/bucket",
    		component: BucketSettingsSubPage,
    		enabled: () => {
    			return !get_store_value( needs_access_keys );
    		},
    		events: {
    			"page.initial.settings": ( data ) => {
    				// We need a bucket and region to have been verified before some pages are useful.
    				if ( data.hasOwnProperty( "location" ) && !get_store_value( needs_access_keys ) && !get_store_value( is_plugin_setup ) ) {
    					for ( const prefix of ["/storage", "/media", "/delivery"] ) {
    						if ( data.location.startsWith( prefix ) ) {
    							return true;
    						}
    					}

    					return data.location === "/";
    				}

    				return false;
    			},
    			"settings.save": ( data ) => {
    				// If currently in /storage/provider route, bucket is always next, assuming storage provider set up correctly.
    				return get_store_value( location$1 ) === "/storage/provider" && !get_store_value( needs_access_keys );
    			}
    		}
    	},
    	{
    		position: 230,
    		name: "security",
    		title: () => get_store_value( strings ).security_tab_title,
    		subNav: true,
    		route: "/storage/security",
    		component: SecuritySubPage,
    		enabled: () => {
    			return get_store_value( is_plugin_setup_with_credentials ) && !get_store_value( storage_provider ).requires_acls;
    		},
    		events: {
    			"settings.save": ( data ) => {
    				// If currently in /storage/bucket route,
    				// and storage provider does not require ACLs,
    				// and bucket wasn't just created during initial set up,
    				// then security is next.
    				if (
    					get_store_value( location$1 ) === "/storage/bucket" &&
    					get_store_value( is_plugin_setup_with_credentials ) &&
    					!get_store_value( storage_provider ).requires_acls &&
    					(
    						!data.hasOwnProperty( "bucketSource" ) ||
    						data.bucketSource !== "new" ||
    						!data.hasOwnProperty( "initialSettings" ) ||
    						!data.initialSettings.hasOwnProperty( "bucket" ) ||
    						data.initialSettings.bucket.length > 0
    					)
    				) {
    					return true;
    				}

    				return false;
    			}
    		}
    	},
    	{
    		position: 300,
    		name: "delivery",
    		route: "/delivery/*",
    		component: DeliveryPage
    	},
    ];

    /* src/amazon-s3-and-cloudfront/ui/components/Upsell.svelte generated by Svelte v3.49.0 */
    const file$b = "src/amazon-s3-and-cloudfront/ui/components/Upsell.svelte";
    const get_call_to_action_note_slot_changes = dirty => ({});
    const get_call_to_action_note_slot_context = ctx => ({});
    const get_call_to_action_slot_changes = dirty => ({});
    const get_call_to_action_slot_context = ctx => ({});

    function get_each_context$1(ctx, list, i) {
    	const child_ctx = ctx.slice();
    	child_ctx[3] = list[i];
    	return child_ctx;
    }

    const get_description_slot_changes = dirty => ({});
    const get_description_slot_context = ctx => ({});
    const get_heading_slot_changes = dirty => ({});
    const get_heading_slot_context = ctx => ({});

    // (19:3) {#each benefits as benefit}
    function create_each_block$1(ctx) {
    	let li;
    	let img;
    	let img_src_value;
    	let img_alt_value;
    	let t0;
    	let span;
    	let t1_value = /*benefit*/ ctx[3].text + "";
    	let t1;
    	let t2;

    	const block = {
    		c: function create() {
    			li = element("li");
    			img = element("img");
    			t0 = space();
    			span = element("span");
    			t1 = text(t1_value);
    			t2 = space();
    			if (!src_url_equal(img.src, img_src_value = /*benefit*/ ctx[3].icon)) attr_dev(img, "src", img_src_value);
    			attr_dev(img, "alt", img_alt_value = /*benefit*/ ctx[3].alt);
    			attr_dev(img, "class", "svelte-5j10or");
    			add_location(img, file$b, 20, 5, 398);
    			add_location(span, file$b, 21, 5, 450);
    			attr_dev(li, "class", "svelte-5j10or");
    			add_location(li, file$b, 19, 4, 388);
    		},
    		m: function mount(target, anchor) {
    			insert_dev(target, li, anchor);
    			append_dev(li, img);
    			append_dev(li, t0);
    			append_dev(li, span);
    			append_dev(span, t1);
    			append_dev(li, t2);
    		},
    		p: function update(ctx, dirty) {
    			if (dirty & /*benefits*/ 1 && !src_url_equal(img.src, img_src_value = /*benefit*/ ctx[3].icon)) {
    				attr_dev(img, "src", img_src_value);
    			}

    			if (dirty & /*benefits*/ 1 && img_alt_value !== (img_alt_value = /*benefit*/ ctx[3].alt)) {
    				attr_dev(img, "alt", img_alt_value);
    			}

    			if (dirty & /*benefits*/ 1 && t1_value !== (t1_value = /*benefit*/ ctx[3].text + "")) set_data_dev(t1, t1_value);
    		},
    		d: function destroy(detaching) {
    			if (detaching) detach_dev(li);
    		}
    	};

    	dispatch_dev("SvelteRegisterBlock", {
    		block,
    		id: create_each_block$1.name,
    		type: "each",
    		source: "(19:3) {#each benefits as benefit}",
    		ctx
    	});

    	return block;
    }

    // (7:0) <Panel name="upsell" class="upsell-panel">
    function create_default_slot$5(ctx) {
    	let div0;
    	let t0;
    	let div6;
    	let div1;
    	let t1;
    	let div2;
    	let t2;
    	let div3;
    	let t3;
    	let div5;
    	let t4;
    	let div4;
    	let current;
    	const heading_slot_template = /*#slots*/ ctx[1].heading;
    	const heading_slot = create_slot(heading_slot_template, ctx, /*$$scope*/ ctx[2], get_heading_slot_context);
    	const description_slot_template = /*#slots*/ ctx[1].description;
    	const description_slot = create_slot(description_slot_template, ctx, /*$$scope*/ ctx[2], get_description_slot_context);
    	let each_value = /*benefits*/ ctx[0];
    	validate_each_argument(each_value);
    	let each_blocks = [];

    	for (let i = 0; i < each_value.length; i += 1) {
    		each_blocks[i] = create_each_block$1(get_each_context$1(ctx, each_value, i));
    	}

    	const call_to_action_slot_template = /*#slots*/ ctx[1]["call-to-action"];
    	const call_to_action_slot = create_slot(call_to_action_slot_template, ctx, /*$$scope*/ ctx[2], get_call_to_action_slot_context);
    	const call_to_action_note_slot_template = /*#slots*/ ctx[1]["call-to-action-note"];
    	const call_to_action_note_slot = create_slot(call_to_action_note_slot_template, ctx, /*$$scope*/ ctx[2], get_call_to_action_note_slot_context);

    	const block = {
    		c: function create() {
    			div0 = element("div");
    			t0 = space();
    			div6 = element("div");
    			div1 = element("div");
    			if (heading_slot) heading_slot.c();
    			t1 = space();
    			div2 = element("div");
    			if (description_slot) description_slot.c();
    			t2 = space();
    			div3 = element("div");

    			for (let i = 0; i < each_blocks.length; i += 1) {
    				each_blocks[i].c();
    			}

    			t3 = space();
    			div5 = element("div");
    			if (call_to_action_slot) call_to_action_slot.c();
    			t4 = space();
    			div4 = element("div");
    			if (call_to_action_note_slot) call_to_action_note_slot.c();
    			attr_dev(div0, "class", "branding");
    			add_location(div0, file$b, 7, 1, 136);
    			attr_dev(div1, "class", "heading svelte-5j10or");
    			add_location(div1, file$b, 9, 2, 190);
    			attr_dev(div2, "class", "description svelte-5j10or");
    			add_location(div2, file$b, 13, 2, 256);
    			attr_dev(div3, "class", "benefits svelte-5j10or");
    			add_location(div3, file$b, 17, 2, 330);
    			attr_dev(div4, "class", "note svelte-5j10or");
    			add_location(div4, file$b, 28, 3, 582);
    			attr_dev(div5, "class", "call-to-action svelte-5j10or");
    			add_location(div5, file$b, 26, 2, 511);
    			attr_dev(div6, "class", "content svelte-5j10or");
    			add_location(div6, file$b, 8, 1, 166);
    		},
    		m: function mount(target, anchor) {
    			insert_dev(target, div0, anchor);
    			insert_dev(target, t0, anchor);
    			insert_dev(target, div6, anchor);
    			append_dev(div6, div1);

    			if (heading_slot) {
    				heading_slot.m(div1, null);
    			}

    			append_dev(div6, t1);
    			append_dev(div6, div2);

    			if (description_slot) {
    				description_slot.m(div2, null);
    			}

    			append_dev(div6, t2);
    			append_dev(div6, div3);

    			for (let i = 0; i < each_blocks.length; i += 1) {
    				each_blocks[i].m(div3, null);
    			}

    			append_dev(div6, t3);
    			append_dev(div6, div5);

    			if (call_to_action_slot) {
    				call_to_action_slot.m(div5, null);
    			}

    			append_dev(div5, t4);
    			append_dev(div5, div4);

    			if (call_to_action_note_slot) {
    				call_to_action_note_slot.m(div4, null);
    			}

    			current = true;
    		},
    		p: function update(ctx, dirty) {
    			if (heading_slot) {
    				if (heading_slot.p && (!current || dirty & /*$$scope*/ 4)) {
    					update_slot_base(
    						heading_slot,
    						heading_slot_template,
    						ctx,
    						/*$$scope*/ ctx[2],
    						!current
    						? get_all_dirty_from_scope(/*$$scope*/ ctx[2])
    						: get_slot_changes(heading_slot_template, /*$$scope*/ ctx[2], dirty, get_heading_slot_changes),
    						get_heading_slot_context
    					);
    				}
    			}

    			if (description_slot) {
    				if (description_slot.p && (!current || dirty & /*$$scope*/ 4)) {
    					update_slot_base(
    						description_slot,
    						description_slot_template,
    						ctx,
    						/*$$scope*/ ctx[2],
    						!current
    						? get_all_dirty_from_scope(/*$$scope*/ ctx[2])
    						: get_slot_changes(description_slot_template, /*$$scope*/ ctx[2], dirty, get_description_slot_changes),
    						get_description_slot_context
    					);
    				}
    			}

    			if (dirty & /*benefits*/ 1) {
    				each_value = /*benefits*/ ctx[0];
    				validate_each_argument(each_value);
    				let i;

    				for (i = 0; i < each_value.length; i += 1) {
    					const child_ctx = get_each_context$1(ctx, each_value, i);

    					if (each_blocks[i]) {
    						each_blocks[i].p(child_ctx, dirty);
    					} else {
    						each_blocks[i] = create_each_block$1(child_ctx);
    						each_blocks[i].c();
    						each_blocks[i].m(div3, null);
    					}
    				}

    				for (; i < each_blocks.length; i += 1) {
    					each_blocks[i].d(1);
    				}

    				each_blocks.length = each_value.length;
    			}

    			if (call_to_action_slot) {
    				if (call_to_action_slot.p && (!current || dirty & /*$$scope*/ 4)) {
    					update_slot_base(
    						call_to_action_slot,
    						call_to_action_slot_template,
    						ctx,
    						/*$$scope*/ ctx[2],
    						!current
    						? get_all_dirty_from_scope(/*$$scope*/ ctx[2])
    						: get_slot_changes(call_to_action_slot_template, /*$$scope*/ ctx[2], dirty, get_call_to_action_slot_changes),
    						get_call_to_action_slot_context
    					);
    				}
    			}

    			if (call_to_action_note_slot) {
    				if (call_to_action_note_slot.p && (!current || dirty & /*$$scope*/ 4)) {
    					update_slot_base(
    						call_to_action_note_slot,
    						call_to_action_note_slot_template,
    						ctx,
    						/*$$scope*/ ctx[2],
    						!current
    						? get_all_dirty_from_scope(/*$$scope*/ ctx[2])
    						: get_slot_changes(call_to_action_note_slot_template, /*$$scope*/ ctx[2], dirty, get_call_to_action_note_slot_changes),
    						get_call_to_action_note_slot_context
    					);
    				}
    			}
    		},
    		i: function intro(local) {
    			if (current) return;
    			transition_in(heading_slot, local);
    			transition_in(description_slot, local);
    			transition_in(call_to_action_slot, local);
    			transition_in(call_to_action_note_slot, local);
    			current = true;
    		},
    		o: function outro(local) {
    			transition_out(heading_slot, local);
    			transition_out(description_slot, local);
    			transition_out(call_to_action_slot, local);
    			transition_out(call_to_action_note_slot, local);
    			current = false;
    		},
    		d: function destroy(detaching) {
    			if (detaching) detach_dev(div0);
    			if (detaching) detach_dev(t0);
    			if (detaching) detach_dev(div6);
    			if (heading_slot) heading_slot.d(detaching);
    			if (description_slot) description_slot.d(detaching);
    			destroy_each(each_blocks, detaching);
    			if (call_to_action_slot) call_to_action_slot.d(detaching);
    			if (call_to_action_note_slot) call_to_action_note_slot.d(detaching);
    		}
    	};

    	dispatch_dev("SvelteRegisterBlock", {
    		block,
    		id: create_default_slot$5.name,
    		type: "slot",
    		source: "(7:0) <Panel name=\\\"upsell\\\" class=\\\"upsell-panel\\\">",
    		ctx
    	});

    	return block;
    }

    function create_fragment$d(ctx) {
    	let panel;
    	let current;

    	panel = new Panel({
    			props: {
    				name: "upsell",
    				class: "upsell-panel",
    				$$slots: { default: [create_default_slot$5] },
    				$$scope: { ctx }
    			},
    			$$inline: true
    		});

    	const block = {
    		c: function create() {
    			create_component(panel.$$.fragment);
    		},
    		l: function claim(nodes) {
    			throw new Error("options.hydrate only works if the component was compiled with the `hydratable: true` option");
    		},
    		m: function mount(target, anchor) {
    			mount_component(panel, target, anchor);
    			current = true;
    		},
    		p: function update(ctx, [dirty]) {
    			const panel_changes = {};

    			if (dirty & /*$$scope, benefits*/ 5) {
    				panel_changes.$$scope = { dirty, ctx };
    			}

    			panel.$set(panel_changes);
    		},
    		i: function intro(local) {
    			if (current) return;
    			transition_in(panel.$$.fragment, local);
    			current = true;
    		},
    		o: function outro(local) {
    			transition_out(panel.$$.fragment, local);
    			current = false;
    		},
    		d: function destroy(detaching) {
    			destroy_component(panel, detaching);
    		}
    	};

    	dispatch_dev("SvelteRegisterBlock", {
    		block,
    		id: create_fragment$d.name,
    		type: "component",
    		source: "",
    		ctx
    	});

    	return block;
    }

    function instance$d($$self, $$props, $$invalidate) {
    	let { $$slots: slots = {}, $$scope } = $$props;
    	validate_slots('Upsell', slots, ['heading','description','call-to-action','call-to-action-note']);
    	let { benefits } = $$props;
    	const writable_props = ['benefits'];

    	Object.keys($$props).forEach(key => {
    		if (!~writable_props.indexOf(key) && key.slice(0, 2) !== '$$' && key !== 'slot') console.warn(`<Upsell> was created with unknown prop '${key}'`);
    	});

    	$$self.$$set = $$props => {
    		if ('benefits' in $$props) $$invalidate(0, benefits = $$props.benefits);
    		if ('$$scope' in $$props) $$invalidate(2, $$scope = $$props.$$scope);
    	};

    	$$self.$capture_state = () => ({ Panel, benefits });

    	$$self.$inject_state = $$props => {
    		if ('benefits' in $$props) $$invalidate(0, benefits = $$props.benefits);
    	};

    	if ($$props && "$$inject" in $$props) {
    		$$self.$inject_state($$props.$$inject);
    	}

    	return [benefits, slots, $$scope];
    }

    class Upsell extends SvelteComponentDev {
    	constructor(options) {
    		super(options);
    		init(this, options, instance$d, create_fragment$d, safe_not_equal, { benefits: 0 });

    		dispatch_dev("SvelteRegisterComponent", {
    			component: this,
    			tagName: "Upsell",
    			options,
    			id: create_fragment$d.name
    		});

    		const { ctx } = this.$$;
    		const props = options.props || {};

    		if (/*benefits*/ ctx[0] === undefined && !('benefits' in props)) {
    			console.warn("<Upsell> was created without expected prop 'benefits'");
    		}
    	}

    	get benefits() {
    		throw new Error("<Upsell>: Props cannot be read directly from the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
    	}

    	set benefits(value) {
    		throw new Error("<Upsell>: Props cannot be set directly on the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
    	}
    }

    /* src/amazon-s3-and-cloudfront/ui/components/AssetsUpgrade.svelte generated by Svelte v3.49.0 */
    const file$a = "src/amazon-s3-and-cloudfront/ui/components/AssetsUpgrade.svelte";

    // (25:1) 
    function create_heading_slot$1(ctx) {
    	let div;
    	let t_value = /*$strings*/ ctx[0].assets_upsell_heading + "";
    	let t;

    	const block = {
    		c: function create() {
    			div = element("div");
    			t = text(t_value);
    			attr_dev(div, "slot", "heading");
    			add_location(div, file$a, 24, 1, 523);
    		},
    		m: function mount(target, anchor) {
    			insert_dev(target, div, anchor);
    			append_dev(div, t);
    		},
    		p: function update(ctx, dirty) {
    			if (dirty & /*$strings*/ 1 && t_value !== (t_value = /*$strings*/ ctx[0].assets_upsell_heading + "")) set_data_dev(t, t_value);
    		},
    		d: function destroy(detaching) {
    			if (detaching) detach_dev(div);
    		}
    	};

    	dispatch_dev("SvelteRegisterBlock", {
    		block,
    		id: create_heading_slot$1.name,
    		type: "slot",
    		source: "(25:1) ",
    		ctx
    	});

    	return block;
    }

    // (27:1) 
    function create_description_slot$1(ctx) {
    	let div;
    	let raw_value = /*$strings*/ ctx[0].assets_upsell_description + "";

    	const block = {
    		c: function create() {
    			div = element("div");
    			attr_dev(div, "slot", "description");
    			add_location(div, file$a, 26, 1, 584);
    		},
    		m: function mount(target, anchor) {
    			insert_dev(target, div, anchor);
    			div.innerHTML = raw_value;
    		},
    		p: function update(ctx, dirty) {
    			if (dirty & /*$strings*/ 1 && raw_value !== (raw_value = /*$strings*/ ctx[0].assets_upsell_description + "")) div.innerHTML = raw_value;		},
    		d: function destroy(detaching) {
    			if (detaching) detach_dev(div);
    		}
    	};

    	dispatch_dev("SvelteRegisterBlock", {
    		block,
    		id: create_description_slot$1.name,
    		type: "slot",
    		source: "(27:1) ",
    		ctx
    	});

    	return block;
    }

    // (29:1) 
    function create_call_to_action_slot$1(ctx) {
    	let a;
    	let img;
    	let img_src_value;
    	let t0;
    	let t1_value = /*$strings*/ ctx[0].assets_upsell_cta + "";
    	let t1;
    	let a_href_value;

    	const block = {
    		c: function create() {
    			a = element("a");
    			img = element("img");
    			t0 = space();
    			t1 = text(t1_value);
    			if (!src_url_equal(img.src, img_src_value = /*$urls*/ ctx[1].assets + "img/icon/stars.svg")) attr_dev(img, "src", img_src_value);
    			attr_dev(img, "alt", "stars icon");
    			set_style(img, "margin-right", "5px");
    			add_location(img, file$a, 29, 2, 757);
    			attr_dev(a, "slot", "call-to-action");
    			attr_dev(a, "href", a_href_value = /*$urls*/ ctx[1].upsell_discount_assets);
    			attr_dev(a, "class", "button btn-lg btn-primary");
    			add_location(a, file$a, 28, 1, 659);
    		},
    		m: function mount(target, anchor) {
    			insert_dev(target, a, anchor);
    			append_dev(a, img);
    			append_dev(a, t0);
    			append_dev(a, t1);
    		},
    		p: function update(ctx, dirty) {
    			if (dirty & /*$urls*/ 2 && !src_url_equal(img.src, img_src_value = /*$urls*/ ctx[1].assets + "img/icon/stars.svg")) {
    				attr_dev(img, "src", img_src_value);
    			}

    			if (dirty & /*$strings*/ 1 && t1_value !== (t1_value = /*$strings*/ ctx[0].assets_upsell_cta + "")) set_data_dev(t1, t1_value);

    			if (dirty & /*$urls*/ 2 && a_href_value !== (a_href_value = /*$urls*/ ctx[1].upsell_discount_assets)) {
    				attr_dev(a, "href", a_href_value);
    			}
    		},
    		d: function destroy(detaching) {
    			if (detaching) detach_dev(a);
    		}
    	};

    	dispatch_dev("SvelteRegisterBlock", {
    		block,
    		id: create_call_to_action_slot$1.name,
    		type: "slot",
    		source: "(29:1) ",
    		ctx
    	});

    	return block;
    }

    function create_fragment$c(ctx) {
    	let upsell;
    	let current;

    	upsell = new Upsell({
    			props: {
    				benefits: /*benefits*/ ctx[2],
    				$$slots: {
    					"call-to-action": [create_call_to_action_slot$1],
    					description: [create_description_slot$1],
    					heading: [create_heading_slot$1]
    				},
    				$$scope: { ctx }
    			},
    			$$inline: true
    		});

    	const block = {
    		c: function create() {
    			create_component(upsell.$$.fragment);
    		},
    		l: function claim(nodes) {
    			throw new Error("options.hydrate only works if the component was compiled with the `hydratable: true` option");
    		},
    		m: function mount(target, anchor) {
    			mount_component(upsell, target, anchor);
    			current = true;
    		},
    		p: function update(ctx, [dirty]) {
    			const upsell_changes = {};

    			if (dirty & /*$$scope, $urls, $strings*/ 11) {
    				upsell_changes.$$scope = { dirty, ctx };
    			}

    			upsell.$set(upsell_changes);
    		},
    		i: function intro(local) {
    			if (current) return;
    			transition_in(upsell.$$.fragment, local);
    			current = true;
    		},
    		o: function outro(local) {
    			transition_out(upsell.$$.fragment, local);
    			current = false;
    		},
    		d: function destroy(detaching) {
    			destroy_component(upsell, detaching);
    		}
    	};

    	dispatch_dev("SvelteRegisterBlock", {
    		block,
    		id: create_fragment$c.name,
    		type: "component",
    		source: "",
    		ctx
    	});

    	return block;
    }

    function instance$c($$self, $$props, $$invalidate) {
    	let $strings;
    	let $urls;
    	validate_store(strings, 'strings');
    	component_subscribe($$self, strings, $$value => $$invalidate(0, $strings = $$value));
    	validate_store(urls, 'urls');
    	component_subscribe($$self, urls, $$value => $$invalidate(1, $urls = $$value));
    	let { $$slots: slots = {}, $$scope } = $$props;
    	validate_slots('AssetsUpgrade', slots, []);

    	let benefits = [
    		{
    			icon: $urls.assets + 'img/icon/fonts.svg',
    			alt: 'js icon',
    			text: $strings.assets_uppsell_benefits.js
    		},
    		{
    			icon: $urls.assets + 'img/icon/css.svg',
    			alt: 'css icon',
    			text: $strings.assets_uppsell_benefits.css
    		},
    		{
    			icon: $urls.assets + 'img/icon/fonts.svg',
    			alt: 'fonts icon',
    			text: $strings.assets_uppsell_benefits.fonts
    		}
    	];

    	const writable_props = [];

    	Object.keys($$props).forEach(key => {
    		if (!~writable_props.indexOf(key) && key.slice(0, 2) !== '$$' && key !== 'slot') console.warn(`<AssetsUpgrade> was created with unknown prop '${key}'`);
    	});

    	$$self.$capture_state = () => ({
    		strings,
    		urls,
    		Upsell,
    		benefits,
    		$strings,
    		$urls
    	});

    	$$self.$inject_state = $$props => {
    		if ('benefits' in $$props) $$invalidate(2, benefits = $$props.benefits);
    	};

    	if ($$props && "$$inject" in $$props) {
    		$$self.$inject_state($$props.$$inject);
    	}

    	return [$strings, $urls, benefits];
    }

    class AssetsUpgrade extends SvelteComponentDev {
    	constructor(options) {
    		super(options);
    		init(this, options, instance$c, create_fragment$c, safe_not_equal, {});

    		dispatch_dev("SvelteRegisterComponent", {
    			component: this,
    			tagName: "AssetsUpgrade",
    			options,
    			id: create_fragment$c.name
    		});
    	}
    }

    /* src/amazon-s3-and-cloudfront/ui/components/AssetsPage.svelte generated by Svelte v3.49.0 */
    const file$9 = "src/amazon-s3-and-cloudfront/ui/components/AssetsPage.svelte";

    // (9:0) <Page {name} on:routeEvent>
    function create_default_slot$4(ctx) {
    	let h2;
    	let t0_value = /*$strings*/ ctx[1].assets_title + "";
    	let t0;
    	let t1;
    	let assetsupgrade;
    	let current;
    	assetsupgrade = new AssetsUpgrade({ $$inline: true });

    	const block = {
    		c: function create() {
    			h2 = element("h2");
    			t0 = text(t0_value);
    			t1 = space();
    			create_component(assetsupgrade.$$.fragment);
    			attr_dev(h2, "class", "page-title");
    			add_location(h2, file$9, 9, 1, 206);
    		},
    		m: function mount(target, anchor) {
    			insert_dev(target, h2, anchor);
    			append_dev(h2, t0);
    			insert_dev(target, t1, anchor);
    			mount_component(assetsupgrade, target, anchor);
    			current = true;
    		},
    		p: function update(ctx, dirty) {
    			if ((!current || dirty & /*$strings*/ 2) && t0_value !== (t0_value = /*$strings*/ ctx[1].assets_title + "")) set_data_dev(t0, t0_value);
    		},
    		i: function intro(local) {
    			if (current) return;
    			transition_in(assetsupgrade.$$.fragment, local);
    			current = true;
    		},
    		o: function outro(local) {
    			transition_out(assetsupgrade.$$.fragment, local);
    			current = false;
    		},
    		d: function destroy(detaching) {
    			if (detaching) detach_dev(h2);
    			if (detaching) detach_dev(t1);
    			destroy_component(assetsupgrade, detaching);
    		}
    	};

    	dispatch_dev("SvelteRegisterBlock", {
    		block,
    		id: create_default_slot$4.name,
    		type: "slot",
    		source: "(9:0) <Page {name} on:routeEvent>",
    		ctx
    	});

    	return block;
    }

    function create_fragment$b(ctx) {
    	let page;
    	let current;

    	page = new Page({
    			props: {
    				name: /*name*/ ctx[0],
    				$$slots: { default: [create_default_slot$4] },
    				$$scope: { ctx }
    			},
    			$$inline: true
    		});

    	page.$on("routeEvent", /*routeEvent_handler*/ ctx[2]);

    	const block = {
    		c: function create() {
    			create_component(page.$$.fragment);
    		},
    		l: function claim(nodes) {
    			throw new Error("options.hydrate only works if the component was compiled with the `hydratable: true` option");
    		},
    		m: function mount(target, anchor) {
    			mount_component(page, target, anchor);
    			current = true;
    		},
    		p: function update(ctx, [dirty]) {
    			const page_changes = {};
    			if (dirty & /*name*/ 1) page_changes.name = /*name*/ ctx[0];

    			if (dirty & /*$$scope, $strings*/ 10) {
    				page_changes.$$scope = { dirty, ctx };
    			}

    			page.$set(page_changes);
    		},
    		i: function intro(local) {
    			if (current) return;
    			transition_in(page.$$.fragment, local);
    			current = true;
    		},
    		o: function outro(local) {
    			transition_out(page.$$.fragment, local);
    			current = false;
    		},
    		d: function destroy(detaching) {
    			destroy_component(page, detaching);
    		}
    	};

    	dispatch_dev("SvelteRegisterBlock", {
    		block,
    		id: create_fragment$b.name,
    		type: "component",
    		source: "",
    		ctx
    	});

    	return block;
    }

    function instance$b($$self, $$props, $$invalidate) {
    	let $strings;
    	validate_store(strings, 'strings');
    	component_subscribe($$self, strings, $$value => $$invalidate(1, $strings = $$value));
    	let { $$slots: slots = {}, $$scope } = $$props;
    	validate_slots('AssetsPage', slots, []);
    	let { name = "assets" } = $$props;
    	const writable_props = ['name'];

    	Object.keys($$props).forEach(key => {
    		if (!~writable_props.indexOf(key) && key.slice(0, 2) !== '$$' && key !== 'slot') console.warn(`<AssetsPage> was created with unknown prop '${key}'`);
    	});

    	function routeEvent_handler(event) {
    		bubble.call(this, $$self, event);
    	}

    	$$self.$$set = $$props => {
    		if ('name' in $$props) $$invalidate(0, name = $$props.name);
    	};

    	$$self.$capture_state = () => ({
    		strings,
    		Page,
    		AssetsUpgrade,
    		name,
    		$strings
    	});

    	$$self.$inject_state = $$props => {
    		if ('name' in $$props) $$invalidate(0, name = $$props.name);
    	};

    	if ($$props && "$$inject" in $$props) {
    		$$self.$inject_state($$props.$$inject);
    	}

    	return [name, $strings, routeEvent_handler];
    }

    class AssetsPage extends SvelteComponentDev {
    	constructor(options) {
    		super(options);
    		init(this, options, instance$b, create_fragment$b, safe_not_equal, { name: 0 });

    		dispatch_dev("SvelteRegisterComponent", {
    			component: this,
    			tagName: "AssetsPage",
    			options,
    			id: create_fragment$b.name
    		});
    	}

    	get name() {
    		throw new Error("<AssetsPage>: Props cannot be read directly from the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
    	}

    	set name(value) {
    		throw new Error("<AssetsPage>: Props cannot be set directly on the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
    	}
    }

    /* src/amazon-s3-and-cloudfront/ui/components/ToolsUpgrade.svelte generated by Svelte v3.49.0 */
    const file$8 = "src/amazon-s3-and-cloudfront/ui/components/ToolsUpgrade.svelte";

    // (30:1) 
    function create_heading_slot(ctx) {
    	let div;
    	let t_value = /*$strings*/ ctx[0].tools_upsell_heading + "";
    	let t;

    	const block = {
    		c: function create() {
    			div = element("div");
    			t = text(t_value);
    			attr_dev(div, "slot", "heading");
    			add_location(div, file$8, 29, 1, 750);
    		},
    		m: function mount(target, anchor) {
    			insert_dev(target, div, anchor);
    			append_dev(div, t);
    		},
    		p: function update(ctx, dirty) {
    			if (dirty & /*$strings*/ 1 && t_value !== (t_value = /*$strings*/ ctx[0].tools_upsell_heading + "")) set_data_dev(t, t_value);
    		},
    		d: function destroy(detaching) {
    			if (detaching) detach_dev(div);
    		}
    	};

    	dispatch_dev("SvelteRegisterBlock", {
    		block,
    		id: create_heading_slot.name,
    		type: "slot",
    		source: "(30:1) ",
    		ctx
    	});

    	return block;
    }

    // (32:1) 
    function create_description_slot(ctx) {
    	let div;
    	let raw_value = /*$strings*/ ctx[0].tools_upsell_description + "";

    	const block = {
    		c: function create() {
    			div = element("div");
    			attr_dev(div, "slot", "description");
    			add_location(div, file$8, 31, 1, 810);
    		},
    		m: function mount(target, anchor) {
    			insert_dev(target, div, anchor);
    			div.innerHTML = raw_value;
    		},
    		p: function update(ctx, dirty) {
    			if (dirty & /*$strings*/ 1 && raw_value !== (raw_value = /*$strings*/ ctx[0].tools_upsell_description + "")) div.innerHTML = raw_value;		},
    		d: function destroy(detaching) {
    			if (detaching) detach_dev(div);
    		}
    	};

    	dispatch_dev("SvelteRegisterBlock", {
    		block,
    		id: create_description_slot.name,
    		type: "slot",
    		source: "(32:1) ",
    		ctx
    	});

    	return block;
    }

    // (34:1) 
    function create_call_to_action_slot(ctx) {
    	let a;
    	let img;
    	let img_src_value;
    	let t0;
    	let t1_value = /*$strings*/ ctx[0].tools_upsell_cta + "";
    	let t1;
    	let a_href_value;

    	const block = {
    		c: function create() {
    			a = element("a");
    			img = element("img");
    			t0 = space();
    			t1 = text(t1_value);
    			if (!src_url_equal(img.src, img_src_value = /*$urls*/ ctx[1].assets + "img/icon/stars.svg")) attr_dev(img, "src", img_src_value);
    			attr_dev(img, "alt", "stars icon");
    			set_style(img, "margin-right", "5px");
    			add_location(img, file$8, 34, 2, 981);
    			attr_dev(a, "slot", "call-to-action");
    			attr_dev(a, "href", a_href_value = /*$urls*/ ctx[1].upsell_discount_tools);
    			attr_dev(a, "class", "button btn-lg btn-primary");
    			add_location(a, file$8, 33, 1, 884);
    		},
    		m: function mount(target, anchor) {
    			insert_dev(target, a, anchor);
    			append_dev(a, img);
    			append_dev(a, t0);
    			append_dev(a, t1);
    		},
    		p: function update(ctx, dirty) {
    			if (dirty & /*$urls*/ 2 && !src_url_equal(img.src, img_src_value = /*$urls*/ ctx[1].assets + "img/icon/stars.svg")) {
    				attr_dev(img, "src", img_src_value);
    			}

    			if (dirty & /*$strings*/ 1 && t1_value !== (t1_value = /*$strings*/ ctx[0].tools_upsell_cta + "")) set_data_dev(t1, t1_value);

    			if (dirty & /*$urls*/ 2 && a_href_value !== (a_href_value = /*$urls*/ ctx[1].upsell_discount_tools)) {
    				attr_dev(a, "href", a_href_value);
    			}
    		},
    		d: function destroy(detaching) {
    			if (detaching) detach_dev(a);
    		}
    	};

    	dispatch_dev("SvelteRegisterBlock", {
    		block,
    		id: create_call_to_action_slot.name,
    		type: "slot",
    		source: "(34:1) ",
    		ctx
    	});

    	return block;
    }

    function create_fragment$a(ctx) {
    	let upsell;
    	let current;

    	upsell = new Upsell({
    			props: {
    				benefits: /*benefits*/ ctx[2],
    				$$slots: {
    					"call-to-action": [create_call_to_action_slot],
    					description: [create_description_slot],
    					heading: [create_heading_slot]
    				},
    				$$scope: { ctx }
    			},
    			$$inline: true
    		});

    	const block = {
    		c: function create() {
    			create_component(upsell.$$.fragment);
    		},
    		l: function claim(nodes) {
    			throw new Error("options.hydrate only works if the component was compiled with the `hydratable: true` option");
    		},
    		m: function mount(target, anchor) {
    			mount_component(upsell, target, anchor);
    			current = true;
    		},
    		p: function update(ctx, [dirty]) {
    			const upsell_changes = {};

    			if (dirty & /*$$scope, $urls, $strings*/ 11) {
    				upsell_changes.$$scope = { dirty, ctx };
    			}

    			upsell.$set(upsell_changes);
    		},
    		i: function intro(local) {
    			if (current) return;
    			transition_in(upsell.$$.fragment, local);
    			current = true;
    		},
    		o: function outro(local) {
    			transition_out(upsell.$$.fragment, local);
    			current = false;
    		},
    		d: function destroy(detaching) {
    			destroy_component(upsell, detaching);
    		}
    	};

    	dispatch_dev("SvelteRegisterBlock", {
    		block,
    		id: create_fragment$a.name,
    		type: "component",
    		source: "",
    		ctx
    	});

    	return block;
    }

    function instance$a($$self, $$props, $$invalidate) {
    	let $strings;
    	let $urls;
    	validate_store(strings, 'strings');
    	component_subscribe($$self, strings, $$value => $$invalidate(0, $strings = $$value));
    	validate_store(urls, 'urls');
    	component_subscribe($$self, urls, $$value => $$invalidate(1, $urls = $$value));
    	let { $$slots: slots = {}, $$scope } = $$props;
    	validate_slots('ToolsUpgrade', slots, []);

    	let benefits = [
    		{
    			icon: $urls.assets + 'img/icon/offload-remaining.svg',
    			alt: 'offload icon',
    			text: $strings.tools_uppsell_benefits.offload
    		},
    		{
    			icon: $urls.assets + 'img/icon/download.svg',
    			alt: 'download icon',
    			text: $strings.tools_uppsell_benefits.download
    		},
    		{
    			icon: $urls.assets + 'img/icon/remove-from-bucket.svg',
    			alt: 'remove from bucket icon',
    			text: $strings.tools_uppsell_benefits.remove_bucket
    		},
    		{
    			icon: $urls.assets + 'img/icon/remove-from-server.svg',
    			alt: 'remove from server icon',
    			text: $strings.tools_uppsell_benefits.remove_server
    		}
    	];

    	const writable_props = [];

    	Object.keys($$props).forEach(key => {
    		if (!~writable_props.indexOf(key) && key.slice(0, 2) !== '$$' && key !== 'slot') console.warn(`<ToolsUpgrade> was created with unknown prop '${key}'`);
    	});

    	$$self.$capture_state = () => ({
    		strings,
    		urls,
    		Upsell,
    		benefits,
    		$strings,
    		$urls
    	});

    	$$self.$inject_state = $$props => {
    		if ('benefits' in $$props) $$invalidate(2, benefits = $$props.benefits);
    	};

    	if ($$props && "$$inject" in $$props) {
    		$$self.$inject_state($$props.$$inject);
    	}

    	return [$strings, $urls, benefits];
    }

    class ToolsUpgrade extends SvelteComponentDev {
    	constructor(options) {
    		super(options);
    		init(this, options, instance$a, create_fragment$a, safe_not_equal, {});

    		dispatch_dev("SvelteRegisterComponent", {
    			component: this,
    			tagName: "ToolsUpgrade",
    			options,
    			id: create_fragment$a.name
    		});
    	}
    }

    /* src/amazon-s3-and-cloudfront/ui/components/ToolsPage.svelte generated by Svelte v3.49.0 */
    const file$7 = "src/amazon-s3-and-cloudfront/ui/components/ToolsPage.svelte";

    // (10:0) <Page {name} on:routeEvent>
    function create_default_slot$3(ctx) {
    	let notifications;
    	let t0;
    	let h2;
    	let t1_value = /*$strings*/ ctx[1].tools_title + "";
    	let t1;
    	let t2;
    	let toolsupgrade;
    	let current;

    	notifications = new Notifications({
    			props: { tab: /*name*/ ctx[0] },
    			$$inline: true
    		});

    	toolsupgrade = new ToolsUpgrade({ $$inline: true });

    	const block = {
    		c: function create() {
    			create_component(notifications.$$.fragment);
    			t0 = space();
    			h2 = element("h2");
    			t1 = text(t1_value);
    			t2 = space();
    			create_component(toolsupgrade.$$.fragment);
    			attr_dev(h2, "class", "page-title");
    			add_location(h2, file$7, 11, 1, 285);
    		},
    		m: function mount(target, anchor) {
    			mount_component(notifications, target, anchor);
    			insert_dev(target, t0, anchor);
    			insert_dev(target, h2, anchor);
    			append_dev(h2, t1);
    			insert_dev(target, t2, anchor);
    			mount_component(toolsupgrade, target, anchor);
    			current = true;
    		},
    		p: function update(ctx, dirty) {
    			const notifications_changes = {};
    			if (dirty & /*name*/ 1) notifications_changes.tab = /*name*/ ctx[0];
    			notifications.$set(notifications_changes);
    			if ((!current || dirty & /*$strings*/ 2) && t1_value !== (t1_value = /*$strings*/ ctx[1].tools_title + "")) set_data_dev(t1, t1_value);
    		},
    		i: function intro(local) {
    			if (current) return;
    			transition_in(notifications.$$.fragment, local);
    			transition_in(toolsupgrade.$$.fragment, local);
    			current = true;
    		},
    		o: function outro(local) {
    			transition_out(notifications.$$.fragment, local);
    			transition_out(toolsupgrade.$$.fragment, local);
    			current = false;
    		},
    		d: function destroy(detaching) {
    			destroy_component(notifications, detaching);
    			if (detaching) detach_dev(t0);
    			if (detaching) detach_dev(h2);
    			if (detaching) detach_dev(t2);
    			destroy_component(toolsupgrade, detaching);
    		}
    	};

    	dispatch_dev("SvelteRegisterBlock", {
    		block,
    		id: create_default_slot$3.name,
    		type: "slot",
    		source: "(10:0) <Page {name} on:routeEvent>",
    		ctx
    	});

    	return block;
    }

    function create_fragment$9(ctx) {
    	let page;
    	let current;

    	page = new Page({
    			props: {
    				name: /*name*/ ctx[0],
    				$$slots: { default: [create_default_slot$3] },
    				$$scope: { ctx }
    			},
    			$$inline: true
    		});

    	page.$on("routeEvent", /*routeEvent_handler*/ ctx[2]);

    	const block = {
    		c: function create() {
    			create_component(page.$$.fragment);
    		},
    		l: function claim(nodes) {
    			throw new Error("options.hydrate only works if the component was compiled with the `hydratable: true` option");
    		},
    		m: function mount(target, anchor) {
    			mount_component(page, target, anchor);
    			current = true;
    		},
    		p: function update(ctx, [dirty]) {
    			const page_changes = {};
    			if (dirty & /*name*/ 1) page_changes.name = /*name*/ ctx[0];

    			if (dirty & /*$$scope, $strings, name*/ 11) {
    				page_changes.$$scope = { dirty, ctx };
    			}

    			page.$set(page_changes);
    		},
    		i: function intro(local) {
    			if (current) return;
    			transition_in(page.$$.fragment, local);
    			current = true;
    		},
    		o: function outro(local) {
    			transition_out(page.$$.fragment, local);
    			current = false;
    		},
    		d: function destroy(detaching) {
    			destroy_component(page, detaching);
    		}
    	};

    	dispatch_dev("SvelteRegisterBlock", {
    		block,
    		id: create_fragment$9.name,
    		type: "component",
    		source: "",
    		ctx
    	});

    	return block;
    }

    function instance$9($$self, $$props, $$invalidate) {
    	let $strings;
    	validate_store(strings, 'strings');
    	component_subscribe($$self, strings, $$value => $$invalidate(1, $strings = $$value));
    	let { $$slots: slots = {}, $$scope } = $$props;
    	validate_slots('ToolsPage', slots, []);
    	let { name = "tools" } = $$props;
    	const writable_props = ['name'];

    	Object.keys($$props).forEach(key => {
    		if (!~writable_props.indexOf(key) && key.slice(0, 2) !== '$$' && key !== 'slot') console.warn(`<ToolsPage> was created with unknown prop '${key}'`);
    	});

    	function routeEvent_handler(event) {
    		bubble.call(this, $$self, event);
    	}

    	$$self.$$set = $$props => {
    		if ('name' in $$props) $$invalidate(0, name = $$props.name);
    	};

    	$$self.$capture_state = () => ({
    		strings,
    		Page,
    		Notifications,
    		ToolsUpgrade,
    		name,
    		$strings
    	});

    	$$self.$inject_state = $$props => {
    		if ('name' in $$props) $$invalidate(0, name = $$props.name);
    	};

    	if ($$props && "$$inject" in $$props) {
    		$$self.$inject_state($$props.$$inject);
    	}

    	return [name, $strings, routeEvent_handler];
    }

    class ToolsPage extends SvelteComponentDev {
    	constructor(options) {
    		super(options);
    		init(this, options, instance$9, create_fragment$9, safe_not_equal, { name: 0 });

    		dispatch_dev("SvelteRegisterComponent", {
    			component: this,
    			tagName: "ToolsPage",
    			options,
    			id: create_fragment$9.name
    		});
    	}

    	get name() {
    		throw new Error("<ToolsPage>: Props cannot be read directly from the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
    	}

    	set name(value) {
    		throw new Error("<ToolsPage>: Props cannot be set directly on the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
    	}
    }

    /* src/amazon-s3-and-cloudfront/ui/components/SupportPage.svelte generated by Svelte v3.49.0 */
    const file$6 = "src/amazon-s3-and-cloudfront/ui/components/SupportPage.svelte";
    const get_footer_slot_changes = dirty => ({});
    const get_footer_slot_context = ctx => ({});
    const get_content_slot_changes = dirty => ({});
    const get_content_slot_context = ctx => ({});
    const get_header_slot_changes = dirty => ({});
    const get_header_slot_context = ctx => ({});

    // (21:1) {#if title}
    function create_if_block$2(ctx) {
    	let h2;
    	let t;

    	const block = {
    		c: function create() {
    			h2 = element("h2");
    			t = text(/*title*/ ctx[1]);
    			attr_dev(h2, "class", "page-title");
    			add_location(h2, file$6, 21, 2, 541);
    		},
    		m: function mount(target, anchor) {
    			insert_dev(target, h2, anchor);
    			append_dev(h2, t);
    		},
    		p: function update(ctx, dirty) {
    			if (dirty & /*title*/ 2) set_data_dev(t, /*title*/ ctx[1]);
    		},
    		d: function destroy(detaching) {
    			if (detaching) detach_dev(h2);
    		}
    	};

    	dispatch_dev("SvelteRegisterBlock", {
    		block,
    		id: create_if_block$2.name,
    		type: "if",
    		source: "(21:1) {#if title}",
    		ctx
    	});

    	return block;
    }

    // (30:25)       
    function fallback_block(ctx) {
    	let div;
    	let p0;
    	let raw0_value = /*$strings*/ ctx[2].no_support + "";
    	let t0;
    	let p1;
    	let raw1_value = /*$strings*/ ctx[2].community_support + "";
    	let t1;
    	let p2;
    	let raw2_value = /*$strings*/ ctx[2].upgrade_for_support + "";
    	let t2;
    	let p3;
    	let raw3_value = /*$strings*/ ctx[2].report_a_bug + "";

    	const block = {
    		c: function create() {
    			div = element("div");
    			p0 = element("p");
    			t0 = space();
    			p1 = element("p");
    			t1 = space();
    			p2 = element("p");
    			t2 = space();
    			p3 = element("p");
    			add_location(p0, file$6, 31, 6, 764);
    			add_location(p1, file$6, 32, 6, 805);
    			add_location(p2, file$6, 33, 6, 853);
    			add_location(p3, file$6, 34, 6, 903);
    			attr_dev(div, "class", "lite-support");
    			add_location(div, file$6, 30, 5, 731);
    		},
    		m: function mount(target, anchor) {
    			insert_dev(target, div, anchor);
    			append_dev(div, p0);
    			p0.innerHTML = raw0_value;
    			append_dev(div, t0);
    			append_dev(div, p1);
    			p1.innerHTML = raw1_value;
    			append_dev(div, t1);
    			append_dev(div, p2);
    			p2.innerHTML = raw2_value;
    			append_dev(div, t2);
    			append_dev(div, p3);
    			p3.innerHTML = raw3_value;
    		},
    		p: function update(ctx, dirty) {
    			if (dirty & /*$strings*/ 4 && raw0_value !== (raw0_value = /*$strings*/ ctx[2].no_support + "")) p0.innerHTML = raw0_value;			if (dirty & /*$strings*/ 4 && raw1_value !== (raw1_value = /*$strings*/ ctx[2].community_support + "")) p1.innerHTML = raw1_value;			if (dirty & /*$strings*/ 4 && raw2_value !== (raw2_value = /*$strings*/ ctx[2].upgrade_for_support + "")) p2.innerHTML = raw2_value;			if (dirty & /*$strings*/ 4 && raw3_value !== (raw3_value = /*$strings*/ ctx[2].report_a_bug + "")) p3.innerHTML = raw3_value;		},
    		d: function destroy(detaching) {
    			if (detaching) detach_dev(div);
    		}
    	};

    	dispatch_dev("SvelteRegisterBlock", {
    		block,
    		id: fallback_block.name,
    		type: "fallback",
    		source: "(30:25)       ",
    		ctx
    	});

    	return block;
    }

    // (19:0) <Page {name} on:routeEvent>
    function create_default_slot$2(ctx) {
    	let notifications;
    	let t0;
    	let t1;
    	let div3;
    	let t2;
    	let div2;
    	let div1;
    	let t3;
    	let div0;
    	let hr;
    	let t4;
    	let h2;
    	let t5_value = /*$strings*/ ctx[2].diagnostic_info_title + "";
    	let t5;
    	let t6;
    	let pre;
    	let t7;
    	let t8;
    	let a;
    	let t9_value = /*$strings*/ ctx[2].download_diagnostics + "";
    	let t9;
    	let a_href_value;
    	let t10;
    	let current;

    	notifications = new Notifications({
    			props: { tab: /*name*/ ctx[0] },
    			$$inline: true
    		});

    	let if_block = /*title*/ ctx[1] && create_if_block$2(ctx);
    	const header_slot_template = /*#slots*/ ctx[5].header;
    	const header_slot = create_slot(header_slot_template, ctx, /*$$scope*/ ctx[7], get_header_slot_context);
    	const content_slot_template = /*#slots*/ ctx[5].content;
    	const content_slot = create_slot(content_slot_template, ctx, /*$$scope*/ ctx[7], get_content_slot_context);
    	const content_slot_or_fallback = content_slot || fallback_block(ctx);
    	const footer_slot_template = /*#slots*/ ctx[5].footer;
    	const footer_slot = create_slot(footer_slot_template, ctx, /*$$scope*/ ctx[7], get_footer_slot_context);

    	const block = {
    		c: function create() {
    			create_component(notifications.$$.fragment);
    			t0 = space();
    			if (if_block) if_block.c();
    			t1 = space();
    			div3 = element("div");
    			if (header_slot) header_slot.c();
    			t2 = space();
    			div2 = element("div");
    			div1 = element("div");
    			if (content_slot_or_fallback) content_slot_or_fallback.c();
    			t3 = space();
    			div0 = element("div");
    			hr = element("hr");
    			t4 = space();
    			h2 = element("h2");
    			t5 = text(t5_value);
    			t6 = space();
    			pre = element("pre");
    			t7 = text(/*$diagnostics*/ ctx[3]);
    			t8 = space();
    			a = element("a");
    			t9 = text(t9_value);
    			t10 = space();
    			if (footer_slot) footer_slot.c();
    			add_location(hr, file$6, 39, 5, 1004);
    			attr_dev(h2, "class", "page-title");
    			add_location(h2, file$6, 40, 5, 1014);
    			add_location(pre, file$6, 41, 5, 1080);
    			attr_dev(a, "href", a_href_value = /*$urls*/ ctx[4].download_diagnostics);
    			attr_dev(a, "class", "button btn-md btn-outline");
    			add_location(a, file$6, 42, 5, 1111);
    			attr_dev(div0, "class", "diagnostic-info");
    			add_location(div0, file$6, 38, 4, 969);
    			attr_dev(div1, "class", "support-form");
    			add_location(div1, file$6, 28, 3, 673);
    			attr_dev(div2, "class", "columns");
    			add_location(div2, file$6, 27, 2, 648);
    			attr_dev(div3, "class", "support-page wrapper");
    			add_location(div3, file$6, 23, 1, 585);
    		},
    		m: function mount(target, anchor) {
    			mount_component(notifications, target, anchor);
    			insert_dev(target, t0, anchor);
    			if (if_block) if_block.m(target, anchor);
    			insert_dev(target, t1, anchor);
    			insert_dev(target, div3, anchor);

    			if (header_slot) {
    				header_slot.m(div3, null);
    			}

    			append_dev(div3, t2);
    			append_dev(div3, div2);
    			append_dev(div2, div1);

    			if (content_slot_or_fallback) {
    				content_slot_or_fallback.m(div1, null);
    			}

    			append_dev(div1, t3);
    			append_dev(div1, div0);
    			append_dev(div0, hr);
    			append_dev(div0, t4);
    			append_dev(div0, h2);
    			append_dev(h2, t5);
    			append_dev(div0, t6);
    			append_dev(div0, pre);
    			append_dev(pre, t7);
    			append_dev(div0, t8);
    			append_dev(div0, a);
    			append_dev(a, t9);
    			append_dev(div2, t10);

    			if (footer_slot) {
    				footer_slot.m(div2, null);
    			}

    			current = true;
    		},
    		p: function update(ctx, dirty) {
    			const notifications_changes = {};
    			if (dirty & /*name*/ 1) notifications_changes.tab = /*name*/ ctx[0];
    			notifications.$set(notifications_changes);

    			if (/*title*/ ctx[1]) {
    				if (if_block) {
    					if_block.p(ctx, dirty);
    				} else {
    					if_block = create_if_block$2(ctx);
    					if_block.c();
    					if_block.m(t1.parentNode, t1);
    				}
    			} else if (if_block) {
    				if_block.d(1);
    				if_block = null;
    			}

    			if (header_slot) {
    				if (header_slot.p && (!current || dirty & /*$$scope*/ 128)) {
    					update_slot_base(
    						header_slot,
    						header_slot_template,
    						ctx,
    						/*$$scope*/ ctx[7],
    						!current
    						? get_all_dirty_from_scope(/*$$scope*/ ctx[7])
    						: get_slot_changes(header_slot_template, /*$$scope*/ ctx[7], dirty, get_header_slot_changes),
    						get_header_slot_context
    					);
    				}
    			}

    			if (content_slot) {
    				if (content_slot.p && (!current || dirty & /*$$scope*/ 128)) {
    					update_slot_base(
    						content_slot,
    						content_slot_template,
    						ctx,
    						/*$$scope*/ ctx[7],
    						!current
    						? get_all_dirty_from_scope(/*$$scope*/ ctx[7])
    						: get_slot_changes(content_slot_template, /*$$scope*/ ctx[7], dirty, get_content_slot_changes),
    						get_content_slot_context
    					);
    				}
    			} else {
    				if (content_slot_or_fallback && content_slot_or_fallback.p && (!current || dirty & /*$strings*/ 4)) {
    					content_slot_or_fallback.p(ctx, !current ? -1 : dirty);
    				}
    			}

    			if ((!current || dirty & /*$strings*/ 4) && t5_value !== (t5_value = /*$strings*/ ctx[2].diagnostic_info_title + "")) set_data_dev(t5, t5_value);
    			if (!current || dirty & /*$diagnostics*/ 8) set_data_dev(t7, /*$diagnostics*/ ctx[3]);
    			if ((!current || dirty & /*$strings*/ 4) && t9_value !== (t9_value = /*$strings*/ ctx[2].download_diagnostics + "")) set_data_dev(t9, t9_value);

    			if (!current || dirty & /*$urls*/ 16 && a_href_value !== (a_href_value = /*$urls*/ ctx[4].download_diagnostics)) {
    				attr_dev(a, "href", a_href_value);
    			}

    			if (footer_slot) {
    				if (footer_slot.p && (!current || dirty & /*$$scope*/ 128)) {
    					update_slot_base(
    						footer_slot,
    						footer_slot_template,
    						ctx,
    						/*$$scope*/ ctx[7],
    						!current
    						? get_all_dirty_from_scope(/*$$scope*/ ctx[7])
    						: get_slot_changes(footer_slot_template, /*$$scope*/ ctx[7], dirty, get_footer_slot_changes),
    						get_footer_slot_context
    					);
    				}
    			}
    		},
    		i: function intro(local) {
    			if (current) return;
    			transition_in(notifications.$$.fragment, local);
    			transition_in(header_slot, local);
    			transition_in(content_slot_or_fallback, local);
    			transition_in(footer_slot, local);
    			current = true;
    		},
    		o: function outro(local) {
    			transition_out(notifications.$$.fragment, local);
    			transition_out(header_slot, local);
    			transition_out(content_slot_or_fallback, local);
    			transition_out(footer_slot, local);
    			current = false;
    		},
    		d: function destroy(detaching) {
    			destroy_component(notifications, detaching);
    			if (detaching) detach_dev(t0);
    			if (if_block) if_block.d(detaching);
    			if (detaching) detach_dev(t1);
    			if (detaching) detach_dev(div3);
    			if (header_slot) header_slot.d(detaching);
    			if (content_slot_or_fallback) content_slot_or_fallback.d(detaching);
    			if (footer_slot) footer_slot.d(detaching);
    		}
    	};

    	dispatch_dev("SvelteRegisterBlock", {
    		block,
    		id: create_default_slot$2.name,
    		type: "slot",
    		source: "(19:0) <Page {name} on:routeEvent>",
    		ctx
    	});

    	return block;
    }

    function create_fragment$8(ctx) {
    	let page;
    	let current;

    	page = new Page({
    			props: {
    				name: /*name*/ ctx[0],
    				$$slots: { default: [create_default_slot$2] },
    				$$scope: { ctx }
    			},
    			$$inline: true
    		});

    	page.$on("routeEvent", /*routeEvent_handler*/ ctx[6]);

    	const block = {
    		c: function create() {
    			create_component(page.$$.fragment);
    		},
    		l: function claim(nodes) {
    			throw new Error("options.hydrate only works if the component was compiled with the `hydratable: true` option");
    		},
    		m: function mount(target, anchor) {
    			mount_component(page, target, anchor);
    			current = true;
    		},
    		p: function update(ctx, [dirty]) {
    			const page_changes = {};
    			if (dirty & /*name*/ 1) page_changes.name = /*name*/ ctx[0];

    			if (dirty & /*$$scope, $urls, $strings, $diagnostics, title, name*/ 159) {
    				page_changes.$$scope = { dirty, ctx };
    			}

    			page.$set(page_changes);
    		},
    		i: function intro(local) {
    			if (current) return;
    			transition_in(page.$$.fragment, local);
    			current = true;
    		},
    		o: function outro(local) {
    			transition_out(page.$$.fragment, local);
    			current = false;
    		},
    		d: function destroy(detaching) {
    			destroy_component(page, detaching);
    		}
    	};

    	dispatch_dev("SvelteRegisterBlock", {
    		block,
    		id: create_fragment$8.name,
    		type: "component",
    		source: "",
    		ctx
    	});

    	return block;
    }

    function instance$8($$self, $$props, $$invalidate) {
    	let $config;
    	let $strings;
    	let $diagnostics;
    	let $urls;
    	validate_store(config, 'config');
    	component_subscribe($$self, config, $$value => $$invalidate(8, $config = $$value));
    	validate_store(strings, 'strings');
    	component_subscribe($$self, strings, $$value => $$invalidate(2, $strings = $$value));
    	validate_store(diagnostics, 'diagnostics');
    	component_subscribe($$self, diagnostics, $$value => $$invalidate(3, $diagnostics = $$value));
    	validate_store(urls, 'urls');
    	component_subscribe($$self, urls, $$value => $$invalidate(4, $urls = $$value));
    	let { $$slots: slots = {}, $$scope } = $$props;
    	validate_slots('SupportPage', slots, ['header','content','footer']);
    	let { name = "support" } = $$props;
    	let { title = $strings.support_tab_title } = $$props;

    	onMount(async () => {
    		const json = await api.get("diagnostics", {});

    		if (json.hasOwnProperty("diagnostics")) {
    			set_store_value(config, $config.diagnostics = json.diagnostics, $config);
    		}
    	});

    	const writable_props = ['name', 'title'];

    	Object.keys($$props).forEach(key => {
    		if (!~writable_props.indexOf(key) && key.slice(0, 2) !== '$$' && key !== 'slot') console.warn(`<SupportPage> was created with unknown prop '${key}'`);
    	});

    	function routeEvent_handler(event) {
    		bubble.call(this, $$self, event);
    	}

    	$$self.$$set = $$props => {
    		if ('name' in $$props) $$invalidate(0, name = $$props.name);
    		if ('title' in $$props) $$invalidate(1, title = $$props.title);
    		if ('$$scope' in $$props) $$invalidate(7, $$scope = $$props.$$scope);
    	};

    	$$self.$capture_state = () => ({
    		onMount,
    		api,
    		config,
    		diagnostics,
    		strings,
    		urls,
    		Page,
    		Notifications,
    		name,
    		title,
    		$config,
    		$strings,
    		$diagnostics,
    		$urls
    	});

    	$$self.$inject_state = $$props => {
    		if ('name' in $$props) $$invalidate(0, name = $$props.name);
    		if ('title' in $$props) $$invalidate(1, title = $$props.title);
    	};

    	if ($$props && "$$inject" in $$props) {
    		$$self.$inject_state($$props.$$inject);
    	}

    	return [name, title, $strings, $diagnostics, $urls, slots, routeEvent_handler, $$scope];
    }

    class SupportPage extends SvelteComponentDev {
    	constructor(options) {
    		super(options);
    		init(this, options, instance$8, create_fragment$8, safe_not_equal, { name: 0, title: 1 });

    		dispatch_dev("SvelteRegisterComponent", {
    			component: this,
    			tagName: "SupportPage",
    			options,
    			id: create_fragment$8.name
    		});
    	}

    	get name() {
    		throw new Error("<SupportPage>: Props cannot be read directly from the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
    	}

    	set name(value) {
    		throw new Error("<SupportPage>: Props cannot be set directly on the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
    	}

    	get title() {
    		throw new Error("<SupportPage>: Props cannot be read directly from the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
    	}

    	set title(value) {
    		throw new Error("<SupportPage>: Props cannot be set directly on the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
    	}
    }

    /**
     * Adds Lite specific pages.
     */
    function addPages() {
    	pages.add(
    		{
    			position: 10,
    			name: "assets",
    			title: () => get_store_value( strings ).assets_tab_title,
    			nav: true,
    			route: "/assets",
    			component: AssetsPage
    		}
    	);
    	pages.add(
    		{
    			position: 20,
    			name: "tools",
    			title: () => get_store_value( strings ).tools_tab_title,
    			nav: true,
    			route: "/tools",
    			component: ToolsPage
    		}
    	);
    	pages.add(
    		{
    			position: 100,
    			name: "support",
    			title: () => get_store_value( strings ).support_tab_title,
    			nav: true,
    			route: "/support",
    			component: SupportPage
    		}
    	);
    }

    const settingsNotifications = {
    	/**
    	 * Process local and server settings to return a new Map of inline notifications.
    	 *
    	 * @param {Map} notifications
    	 * @param {Object} settings
    	 * @param {Object} current_settings
    	 * @param {Object} strings
    	 *
    	 * @return {Map<string, Map<string, Object>>} keyed by setting name, containing map of notification objects keyed by id.
    	 */
    	process: ( notifications, settings, current_settings, strings ) => {
    		// remove-local-file
    		if ( settings.hasOwnProperty( "remove-local-file" ) && settings[ "remove-local-file" ] ) {
    			let entries = notifications.has( "remove-local-file" ) ? notifications.get( "remove-local-file" ) : new Map();

    			if ( settings.hasOwnProperty( "serve-from-s3" ) && !settings[ "serve-from-s3" ] ) {
    				if ( !entries.has( "lost-files-notice" ) ) {
    					entries.set( "lost-files-notice", {
    						inline: true,
    						type: "error",
    						heading: strings.lost_files_notice_heading,
    						message: strings.lost_files_notice_message
    					} );
    				}
    			} else {
    				entries.delete( "lost-files-notice" );
    			}

    			// Show inline warning about potential compatibility issues
    			// when turning on setting for the first time.
    			if (
    				!entries.has( "remove-local-file-notice" ) &&
    				current_settings.hasOwnProperty( "remove-local-file" ) &&
    				!current_settings[ "remove-local-file" ]
    			) {
    				entries.set( "remove-local-file-notice", {
    					inline: true,
    					type: "warning",
    					message: strings.remove_local_file_message
    				} );
    			}

    			notifications.set( "remove-local-file", entries );
    		} else {
    			notifications.delete( "remove-local-file" );
    		}

    		return notifications;
    	}
    };

    /* src/amazon-s3-and-cloudfront/ui/components/Header.svelte generated by Svelte v3.49.0 */
    const file$5 = "src/amazon-s3-and-cloudfront/ui/components/Header.svelte";

    function create_fragment$7(ctx) {
    	let div2;
    	let div1;
    	let img;
    	let img_src_value;
    	let img_alt_value;
    	let t0;
    	let h1;
    	let t1_value = /*$config*/ ctx[1].title + "";
    	let t1;
    	let t2;
    	let div0;
    	let current;
    	const default_slot_template = /*#slots*/ ctx[4].default;
    	const default_slot = create_slot(default_slot_template, ctx, /*$$scope*/ ctx[3], null);

    	const block = {
    		c: function create() {
    			div2 = element("div");
    			div1 = element("div");
    			img = element("img");
    			t0 = space();
    			h1 = element("h1");
    			t1 = text(t1_value);
    			t2 = space();
    			div0 = element("div");
    			if (default_slot) default_slot.c();
    			attr_dev(img, "class", "medallion");
    			if (!src_url_equal(img.src, img_src_value = /*header_img_url*/ ctx[0])) attr_dev(img, "src", img_src_value);
    			attr_dev(img, "alt", img_alt_value = "" + (/*$config*/ ctx[1].title + " logo"));
    			add_location(img, file$5, 8, 2, 185);
    			add_location(h1, file$5, 9, 2, 259);
    			attr_dev(div0, "class", "licence");
    			add_location(div0, file$5, 10, 2, 286);
    			attr_dev(div1, "class", "header-wrapper");
    			add_location(div1, file$5, 7, 1, 154);
    			attr_dev(div2, "class", "header");
    			add_location(div2, file$5, 6, 0, 132);
    		},
    		l: function claim(nodes) {
    			throw new Error("options.hydrate only works if the component was compiled with the `hydratable: true` option");
    		},
    		m: function mount(target, anchor) {
    			insert_dev(target, div2, anchor);
    			append_dev(div2, div1);
    			append_dev(div1, img);
    			append_dev(div1, t0);
    			append_dev(div1, h1);
    			append_dev(h1, t1);
    			append_dev(div1, t2);
    			append_dev(div1, div0);

    			if (default_slot) {
    				default_slot.m(div0, null);
    			}

    			current = true;
    		},
    		p: function update(ctx, [dirty]) {
    			if (!current || dirty & /*header_img_url*/ 1 && !src_url_equal(img.src, img_src_value = /*header_img_url*/ ctx[0])) {
    				attr_dev(img, "src", img_src_value);
    			}

    			if (!current || dirty & /*$config*/ 2 && img_alt_value !== (img_alt_value = "" + (/*$config*/ ctx[1].title + " logo"))) {
    				attr_dev(img, "alt", img_alt_value);
    			}

    			if ((!current || dirty & /*$config*/ 2) && t1_value !== (t1_value = /*$config*/ ctx[1].title + "")) set_data_dev(t1, t1_value);

    			if (default_slot) {
    				if (default_slot.p && (!current || dirty & /*$$scope*/ 8)) {
    					update_slot_base(
    						default_slot,
    						default_slot_template,
    						ctx,
    						/*$$scope*/ ctx[3],
    						!current
    						? get_all_dirty_from_scope(/*$$scope*/ ctx[3])
    						: get_slot_changes(default_slot_template, /*$$scope*/ ctx[3], dirty, null),
    						null
    					);
    				}
    			}
    		},
    		i: function intro(local) {
    			if (current) return;
    			transition_in(default_slot, local);
    			current = true;
    		},
    		o: function outro(local) {
    			transition_out(default_slot, local);
    			current = false;
    		},
    		d: function destroy(detaching) {
    			if (detaching) detach_dev(div2);
    			if (default_slot) default_slot.d(detaching);
    		}
    	};

    	dispatch_dev("SvelteRegisterBlock", {
    		block,
    		id: create_fragment$7.name,
    		type: "component",
    		source: "",
    		ctx
    	});

    	return block;
    }

    function instance$7($$self, $$props, $$invalidate) {
    	let header_img_url;
    	let $urls;
    	let $config;
    	validate_store(urls, 'urls');
    	component_subscribe($$self, urls, $$value => $$invalidate(2, $urls = $$value));
    	validate_store(config, 'config');
    	component_subscribe($$self, config, $$value => $$invalidate(1, $config = $$value));
    	let { $$slots: slots = {}, $$scope } = $$props;
    	validate_slots('Header', slots, ['default']);
    	const writable_props = [];

    	Object.keys($$props).forEach(key => {
    		if (!~writable_props.indexOf(key) && key.slice(0, 2) !== '$$' && key !== 'slot') console.warn(`<Header> was created with unknown prop '${key}'`);
    	});

    	$$self.$$set = $$props => {
    		if ('$$scope' in $$props) $$invalidate(3, $$scope = $$props.$$scope);
    	};

    	$$self.$capture_state = () => ({
    		config,
    		urls,
    		header_img_url,
    		$urls,
    		$config
    	});

    	$$self.$inject_state = $$props => {
    		if ('header_img_url' in $$props) $$invalidate(0, header_img_url = $$props.header_img_url);
    	};

    	if ($$props && "$$inject" in $$props) {
    		$$self.$inject_state($$props.$$inject);
    	}

    	$$self.$$.update = () => {
    		if ($$self.$$.dirty & /*$urls*/ 4) {
    			$$invalidate(0, header_img_url = $urls.assets + "img/brand/ome-medallion.svg");
    		}
    	};

    	return [header_img_url, $config, $urls, $$scope, slots];
    }

    class Header extends SvelteComponentDev {
    	constructor(options) {
    		super(options);
    		init(this, options, instance$7, create_fragment$7, safe_not_equal, {});

    		dispatch_dev("SvelteRegisterComponent", {
    			component: this,
    			tagName: "Header",
    			options,
    			id: create_fragment$7.name
    		});
    	}
    }

    /* src/amazon-s3-and-cloudfront/ui/components/Settings.svelte generated by Svelte v3.49.0 */

    // (29:0) {#if header}
    function create_if_block_1(ctx) {
    	let switch_instance;
    	let switch_instance_anchor;
    	let current;
    	var switch_value = /*header*/ ctx[0];

    	function switch_props(ctx) {
    		return { $$inline: true };
    	}

    	if (switch_value) {
    		switch_instance = new switch_value(switch_props());
    	}

    	const block = {
    		c: function create() {
    			if (switch_instance) create_component(switch_instance.$$.fragment);
    			switch_instance_anchor = empty();
    		},
    		m: function mount(target, anchor) {
    			if (switch_instance) {
    				mount_component(switch_instance, target, anchor);
    			}

    			insert_dev(target, switch_instance_anchor, anchor);
    			current = true;
    		},
    		p: function update(ctx, dirty) {
    			if (switch_value !== (switch_value = /*header*/ ctx[0])) {
    				if (switch_instance) {
    					group_outros();
    					const old_component = switch_instance;

    					transition_out(old_component.$$.fragment, 1, 0, () => {
    						destroy_component(old_component, 1);
    					});

    					check_outros();
    				}

    				if (switch_value) {
    					switch_instance = new switch_value(switch_props());
    					create_component(switch_instance.$$.fragment);
    					transition_in(switch_instance.$$.fragment, 1);
    					mount_component(switch_instance, switch_instance_anchor.parentNode, switch_instance_anchor);
    				} else {
    					switch_instance = null;
    				}
    			}
    		},
    		i: function intro(local) {
    			if (current) return;
    			if (switch_instance) transition_in(switch_instance.$$.fragment, local);
    			current = true;
    		},
    		o: function outro(local) {
    			if (switch_instance) transition_out(switch_instance.$$.fragment, local);
    			current = false;
    		},
    		d: function destroy(detaching) {
    			if (detaching) detach_dev(switch_instance_anchor);
    			if (switch_instance) destroy_component(switch_instance, detaching);
    		}
    	};

    	dispatch_dev("SvelteRegisterBlock", {
    		block,
    		id: create_if_block_1.name,
    		type: "if",
    		source: "(29:0) {#if header}",
    		ctx
    	});

    	return block;
    }

    // (35:0) {#if footer}
    function create_if_block$1(ctx) {
    	let switch_instance;
    	let switch_instance_anchor;
    	let current;
    	var switch_value = /*footer*/ ctx[1];

    	function switch_props(ctx) {
    		return { $$inline: true };
    	}

    	if (switch_value) {
    		switch_instance = new switch_value(switch_props());
    	}

    	const block = {
    		c: function create() {
    			if (switch_instance) create_component(switch_instance.$$.fragment);
    			switch_instance_anchor = empty();
    		},
    		m: function mount(target, anchor) {
    			if (switch_instance) {
    				mount_component(switch_instance, target, anchor);
    			}

    			insert_dev(target, switch_instance_anchor, anchor);
    			current = true;
    		},
    		p: function update(ctx, dirty) {
    			if (switch_value !== (switch_value = /*footer*/ ctx[1])) {
    				if (switch_instance) {
    					group_outros();
    					const old_component = switch_instance;

    					transition_out(old_component.$$.fragment, 1, 0, () => {
    						destroy_component(old_component, 1);
    					});

    					check_outros();
    				}

    				if (switch_value) {
    					switch_instance = new switch_value(switch_props());
    					create_component(switch_instance.$$.fragment);
    					transition_in(switch_instance.$$.fragment, 1);
    					mount_component(switch_instance, switch_instance_anchor.parentNode, switch_instance_anchor);
    				} else {
    					switch_instance = null;
    				}
    			}
    		},
    		i: function intro(local) {
    			if (current) return;
    			if (switch_instance) transition_in(switch_instance.$$.fragment, local);
    			current = true;
    		},
    		o: function outro(local) {
    			if (switch_instance) transition_out(switch_instance.$$.fragment, local);
    			current = false;
    		},
    		d: function destroy(detaching) {
    			if (detaching) detach_dev(switch_instance_anchor);
    			if (switch_instance) destroy_component(switch_instance, detaching);
    		}
    	};

    	dispatch_dev("SvelteRegisterBlock", {
    		block,
    		id: create_if_block$1.name,
    		type: "if",
    		source: "(35:0) {#if footer}",
    		ctx
    	});

    	return block;
    }

    function create_fragment$6(ctx) {
    	let t0;
    	let t1;
    	let if_block1_anchor;
    	let current;
    	let if_block0 = /*header*/ ctx[0] && create_if_block_1(ctx);
    	const default_slot_template = /*#slots*/ ctx[3].default;
    	const default_slot = create_slot(default_slot_template, ctx, /*$$scope*/ ctx[2], null);
    	let if_block1 = /*footer*/ ctx[1] && create_if_block$1(ctx);

    	const block = {
    		c: function create() {
    			if (if_block0) if_block0.c();
    			t0 = space();
    			if (default_slot) default_slot.c();
    			t1 = space();
    			if (if_block1) if_block1.c();
    			if_block1_anchor = empty();
    		},
    		l: function claim(nodes) {
    			throw new Error("options.hydrate only works if the component was compiled with the `hydratable: true` option");
    		},
    		m: function mount(target, anchor) {
    			if (if_block0) if_block0.m(target, anchor);
    			insert_dev(target, t0, anchor);

    			if (default_slot) {
    				default_slot.m(target, anchor);
    			}

    			insert_dev(target, t1, anchor);
    			if (if_block1) if_block1.m(target, anchor);
    			insert_dev(target, if_block1_anchor, anchor);
    			current = true;
    		},
    		p: function update(ctx, [dirty]) {
    			if (/*header*/ ctx[0]) {
    				if (if_block0) {
    					if_block0.p(ctx, dirty);

    					if (dirty & /*header*/ 1) {
    						transition_in(if_block0, 1);
    					}
    				} else {
    					if_block0 = create_if_block_1(ctx);
    					if_block0.c();
    					transition_in(if_block0, 1);
    					if_block0.m(t0.parentNode, t0);
    				}
    			} else if (if_block0) {
    				group_outros();

    				transition_out(if_block0, 1, 1, () => {
    					if_block0 = null;
    				});

    				check_outros();
    			}

    			if (default_slot) {
    				if (default_slot.p && (!current || dirty & /*$$scope*/ 4)) {
    					update_slot_base(
    						default_slot,
    						default_slot_template,
    						ctx,
    						/*$$scope*/ ctx[2],
    						!current
    						? get_all_dirty_from_scope(/*$$scope*/ ctx[2])
    						: get_slot_changes(default_slot_template, /*$$scope*/ ctx[2], dirty, null),
    						null
    					);
    				}
    			}

    			if (/*footer*/ ctx[1]) {
    				if (if_block1) {
    					if_block1.p(ctx, dirty);

    					if (dirty & /*footer*/ 2) {
    						transition_in(if_block1, 1);
    					}
    				} else {
    					if_block1 = create_if_block$1(ctx);
    					if_block1.c();
    					transition_in(if_block1, 1);
    					if_block1.m(if_block1_anchor.parentNode, if_block1_anchor);
    				}
    			} else if (if_block1) {
    				group_outros();

    				transition_out(if_block1, 1, 1, () => {
    					if_block1 = null;
    				});

    				check_outros();
    			}
    		},
    		i: function intro(local) {
    			if (current) return;
    			transition_in(if_block0);
    			transition_in(default_slot, local);
    			transition_in(if_block1);
    			current = true;
    		},
    		o: function outro(local) {
    			transition_out(if_block0);
    			transition_out(default_slot, local);
    			transition_out(if_block1);
    			current = false;
    		},
    		d: function destroy(detaching) {
    			if (if_block0) if_block0.d(detaching);
    			if (detaching) detach_dev(t0);
    			if (default_slot) default_slot.d(detaching);
    			if (detaching) detach_dev(t1);
    			if (if_block1) if_block1.d(detaching);
    			if (detaching) detach_dev(if_block1_anchor);
    		}
    	};

    	dispatch_dev("SvelteRegisterBlock", {
    		block,
    		id: create_fragment$6.name,
    		type: "component",
    		source: "",
    		ctx
    	});

    	return block;
    }

    function instance$6($$self, $$props, $$invalidate) {
    	let $config;
    	validate_store(config, 'config');
    	component_subscribe($$self, config, $$value => $$invalidate(4, $config = $$value));
    	let { $$slots: slots = {}, $$scope } = $$props;
    	validate_slots('Settings', slots, ['default']);
    	let { header = Header } = $$props;
    	let { footer = null } = $$props;

    	// We need a disassociated copy of the initial settings to work with.
    	settings.set({ ...$config.settings });

    	// We might have some initial notifications to display too.
    	if ($config.notifications.length) {
    		for (const notification of $config.notifications) {
    			notifications.add(notification);
    		}
    	}

    	onMount(() => {
    		// Periodically check the state.
    		state.startPeriodicFetch();

    		// Be a good citizen and clean up the timer when exiting our settings.
    		return () => state.stopPeriodicFetch();
    	});

    	const writable_props = ['header', 'footer'];

    	Object.keys($$props).forEach(key => {
    		if (!~writable_props.indexOf(key) && key.slice(0, 2) !== '$$' && key !== 'slot') console.warn(`<Settings> was created with unknown prop '${key}'`);
    	});

    	$$self.$$set = $$props => {
    		if ('header' in $$props) $$invalidate(0, header = $$props.header);
    		if ('footer' in $$props) $$invalidate(1, footer = $$props.footer);
    		if ('$$scope' in $$props) $$invalidate(2, $$scope = $$props.$$scope);
    	};

    	$$self.$capture_state = () => ({
    		onMount,
    		config,
    		notifications,
    		settings,
    		state,
    		Header,
    		header,
    		footer,
    		$config
    	});

    	$$self.$inject_state = $$props => {
    		if ('header' in $$props) $$invalidate(0, header = $$props.header);
    		if ('footer' in $$props) $$invalidate(1, footer = $$props.footer);
    	};

    	if ($$props && "$$inject" in $$props) {
    		$$self.$inject_state($$props.$$inject);
    	}

    	return [header, footer, $$scope, slots];
    }

    class Settings extends SvelteComponentDev {
    	constructor(options) {
    		super(options);
    		init(this, options, instance$6, create_fragment$6, safe_not_equal, { header: 0, footer: 1 });

    		dispatch_dev("SvelteRegisterComponent", {
    			component: this,
    			tagName: "Settings",
    			options,
    			id: create_fragment$6.name
    		});
    	}

    	get header() {
    		throw new Error("<Settings>: Props cannot be read directly from the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
    	}

    	set header(value) {
    		throw new Error("<Settings>: Props cannot be set directly on the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
    	}

    	get footer() {
    		throw new Error("<Settings>: Props cannot be read directly from the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
    	}

    	set footer(value) {
    		throw new Error("<Settings>: Props cannot be set directly on the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
    	}
    }

    /* src/amazon-s3-and-cloudfront/ui/lite/Header.svelte generated by Svelte v3.49.0 */
    const file$4 = "src/amazon-s3-and-cloudfront/ui/lite/Header.svelte";

    // (6:0) <Header>
    function create_default_slot$1(ctx) {
    	let a;
    	let t_value = /*$strings*/ ctx[1].get_licence_discount_text + "";
    	let t;
    	let a_href_value;

    	const block = {
    		c: function create() {
    			a = element("a");
    			t = text(t_value);
    			attr_dev(a, "href", a_href_value = /*$urls*/ ctx[0].header_discount);
    			attr_dev(a, "class", "button btn-lg btn-primary");
    			add_location(a, file$4, 6, 1, 126);
    		},
    		m: function mount(target, anchor) {
    			insert_dev(target, a, anchor);
    			append_dev(a, t);
    		},
    		p: function update(ctx, dirty) {
    			if (dirty & /*$strings*/ 2 && t_value !== (t_value = /*$strings*/ ctx[1].get_licence_discount_text + "")) set_data_dev(t, t_value);

    			if (dirty & /*$urls*/ 1 && a_href_value !== (a_href_value = /*$urls*/ ctx[0].header_discount)) {
    				attr_dev(a, "href", a_href_value);
    			}
    		},
    		d: function destroy(detaching) {
    			if (detaching) detach_dev(a);
    		}
    	};

    	dispatch_dev("SvelteRegisterBlock", {
    		block,
    		id: create_default_slot$1.name,
    		type: "slot",
    		source: "(6:0) <Header>",
    		ctx
    	});

    	return block;
    }

    function create_fragment$5(ctx) {
    	let header;
    	let current;

    	header = new Header({
    			props: {
    				$$slots: { default: [create_default_slot$1] },
    				$$scope: { ctx }
    			},
    			$$inline: true
    		});

    	const block = {
    		c: function create() {
    			create_component(header.$$.fragment);
    		},
    		l: function claim(nodes) {
    			throw new Error("options.hydrate only works if the component was compiled with the `hydratable: true` option");
    		},
    		m: function mount(target, anchor) {
    			mount_component(header, target, anchor);
    			current = true;
    		},
    		p: function update(ctx, [dirty]) {
    			const header_changes = {};

    			if (dirty & /*$$scope, $urls, $strings*/ 7) {
    				header_changes.$$scope = { dirty, ctx };
    			}

    			header.$set(header_changes);
    		},
    		i: function intro(local) {
    			if (current) return;
    			transition_in(header.$$.fragment, local);
    			current = true;
    		},
    		o: function outro(local) {
    			transition_out(header.$$.fragment, local);
    			current = false;
    		},
    		d: function destroy(detaching) {
    			destroy_component(header, detaching);
    		}
    	};

    	dispatch_dev("SvelteRegisterBlock", {
    		block,
    		id: create_fragment$5.name,
    		type: "component",
    		source: "",
    		ctx
    	});

    	return block;
    }

    function instance$5($$self, $$props, $$invalidate) {
    	let $urls;
    	let $strings;
    	validate_store(urls, 'urls');
    	component_subscribe($$self, urls, $$value => $$invalidate(0, $urls = $$value));
    	validate_store(strings, 'strings');
    	component_subscribe($$self, strings, $$value => $$invalidate(1, $strings = $$value));
    	let { $$slots: slots = {}, $$scope } = $$props;
    	validate_slots('Header', slots, []);
    	const writable_props = [];

    	Object.keys($$props).forEach(key => {
    		if (!~writable_props.indexOf(key) && key.slice(0, 2) !== '$$' && key !== 'slot') console.warn(`<Header> was created with unknown prop '${key}'`);
    	});

    	$$self.$capture_state = () => ({ strings, urls, Header, $urls, $strings });
    	return [$urls, $strings];
    }

    class Header_1 extends SvelteComponentDev {
    	constructor(options) {
    		super(options);
    		init(this, options, instance$5, create_fragment$5, safe_not_equal, {});

    		dispatch_dev("SvelteRegisterComponent", {
    			component: this,
    			tagName: "Header_1",
    			options,
    			id: create_fragment$5.name
    		});
    	}
    }

    /* src/amazon-s3-and-cloudfront/ui/components/NavItem.svelte generated by Svelte v3.49.0 */
    const file$3 = "src/amazon-s3-and-cloudfront/ui/components/NavItem.svelte";

    function create_fragment$4(ctx) {
    	let li;
    	let a;
    	let t_value = /*tab*/ ctx[0].title() + "";
    	let t;
    	let a_href_value;
    	let a_title_value;
    	let active_action;
    	let mounted;
    	let dispose;

    	const block = {
    		c: function create() {
    			li = element("li");
    			a = element("a");
    			t = text(t_value);
    			attr_dev(a, "href", a_href_value = /*tab*/ ctx[0].route);
    			attr_dev(a, "title", a_title_value = /*tab*/ ctx[0].title());
    			add_location(a, file$3, 11, 1, 276);
    			attr_dev(li, "class", "nav-item");
    			toggle_class(li, "focus", /*focus*/ ctx[1]);
    			toggle_class(li, "hover", /*hover*/ ctx[2]);
    			add_location(li, file$3, 10, 0, 168);
    		},
    		l: function claim(nodes) {
    			throw new Error("options.hydrate only works if the component was compiled with the `hydratable: true` option");
    		},
    		m: function mount(target, anchor) {
    			insert_dev(target, li, anchor);
    			append_dev(li, a);
    			append_dev(a, t);

    			if (!mounted) {
    				dispose = [
    					action_destroyer(link.call(null, a)),
    					listen_dev(a, "focusin", /*focusin_handler*/ ctx[3], false, false, false),
    					listen_dev(a, "focusout", /*focusout_handler*/ ctx[4], false, false, false),
    					listen_dev(a, "mouseenter", /*mouseenter_handler*/ ctx[5], false, false, false),
    					listen_dev(a, "mouseleave", /*mouseleave_handler*/ ctx[6], false, false, false),
    					action_destroyer(active_action = active.call(null, li, /*tab*/ ctx[0].routeMatcher
    					? /*tab*/ ctx[0].routeMatcher
    					: /*tab*/ ctx[0].route))
    				];

    				mounted = true;
    			}
    		},
    		p: function update(ctx, [dirty]) {
    			if (dirty & /*tab*/ 1 && t_value !== (t_value = /*tab*/ ctx[0].title() + "")) set_data_dev(t, t_value);

    			if (dirty & /*tab*/ 1 && a_href_value !== (a_href_value = /*tab*/ ctx[0].route)) {
    				attr_dev(a, "href", a_href_value);
    			}

    			if (dirty & /*tab*/ 1 && a_title_value !== (a_title_value = /*tab*/ ctx[0].title())) {
    				attr_dev(a, "title", a_title_value);
    			}

    			if (active_action && is_function(active_action.update) && dirty & /*tab*/ 1) active_action.update.call(null, /*tab*/ ctx[0].routeMatcher
    			? /*tab*/ ctx[0].routeMatcher
    			: /*tab*/ ctx[0].route);

    			if (dirty & /*focus*/ 2) {
    				toggle_class(li, "focus", /*focus*/ ctx[1]);
    			}

    			if (dirty & /*hover*/ 4) {
    				toggle_class(li, "hover", /*hover*/ ctx[2]);
    			}
    		},
    		i: noop,
    		o: noop,
    		d: function destroy(detaching) {
    			if (detaching) detach_dev(li);
    			mounted = false;
    			run_all(dispose);
    		}
    	};

    	dispatch_dev("SvelteRegisterBlock", {
    		block,
    		id: create_fragment$4.name,
    		type: "component",
    		source: "",
    		ctx
    	});

    	return block;
    }

    function instance$4($$self, $$props, $$invalidate) {
    	let { $$slots: slots = {}, $$scope } = $$props;
    	validate_slots('NavItem', slots, []);
    	let { tab } = $$props;
    	let focus = false;
    	let hover = false;
    	const writable_props = ['tab'];

    	Object.keys($$props).forEach(key => {
    		if (!~writable_props.indexOf(key) && key.slice(0, 2) !== '$$' && key !== 'slot') console.warn(`<NavItem> was created with unknown prop '${key}'`);
    	});

    	const focusin_handler = () => $$invalidate(1, focus = true);
    	const focusout_handler = () => $$invalidate(1, focus = false);
    	const mouseenter_handler = () => $$invalidate(2, hover = true);
    	const mouseleave_handler = () => $$invalidate(2, hover = false);

    	$$self.$$set = $$props => {
    		if ('tab' in $$props) $$invalidate(0, tab = $$props.tab);
    	};

    	$$self.$capture_state = () => ({ link, active, tab, focus, hover });

    	$$self.$inject_state = $$props => {
    		if ('tab' in $$props) $$invalidate(0, tab = $$props.tab);
    		if ('focus' in $$props) $$invalidate(1, focus = $$props.focus);
    		if ('hover' in $$props) $$invalidate(2, hover = $$props.hover);
    	};

    	if ($$props && "$$inject" in $$props) {
    		$$self.$inject_state($$props.$$inject);
    	}

    	return [
    		tab,
    		focus,
    		hover,
    		focusin_handler,
    		focusout_handler,
    		mouseenter_handler,
    		mouseleave_handler
    	];
    }

    class NavItem extends SvelteComponentDev {
    	constructor(options) {
    		super(options);
    		init(this, options, instance$4, create_fragment$4, safe_not_equal, { tab: 0 });

    		dispatch_dev("SvelteRegisterComponent", {
    			component: this,
    			tagName: "NavItem",
    			options,
    			id: create_fragment$4.name
    		});

    		const { ctx } = this.$$;
    		const props = options.props || {};

    		if (/*tab*/ ctx[0] === undefined && !('tab' in props)) {
    			console.warn("<NavItem> was created without expected prop 'tab'");
    		}
    	}

    	get tab() {
    		throw new Error("<NavItem>: Props cannot be read directly from the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
    	}

    	set tab(value) {
    		throw new Error("<NavItem>: Props cannot be set directly on the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
    	}
    }

    /* src/amazon-s3-and-cloudfront/ui/components/Nav.svelte generated by Svelte v3.49.0 */
    const file$2 = "src/amazon-s3-and-cloudfront/ui/components/Nav.svelte";

    function get_each_context(ctx, list, i) {
    	const child_ctx = ctx.slice();
    	child_ctx[3] = list[i];
    	return child_ctx;
    }

    // (10:4) {#if tab.nav && tab.title}
    function create_if_block(ctx) {
    	let navitem;
    	let current;

    	navitem = new NavItem({
    			props: { tab: /*tab*/ ctx[3] },
    			$$inline: true
    		});

    	const block = {
    		c: function create() {
    			create_component(navitem.$$.fragment);
    		},
    		m: function mount(target, anchor) {
    			mount_component(navitem, target, anchor);
    			current = true;
    		},
    		p: function update(ctx, dirty) {
    			const navitem_changes = {};
    			if (dirty & /*$pages*/ 1) navitem_changes.tab = /*tab*/ ctx[3];
    			navitem.$set(navitem_changes);
    		},
    		i: function intro(local) {
    			if (current) return;
    			transition_in(navitem.$$.fragment, local);
    			current = true;
    		},
    		o: function outro(local) {
    			transition_out(navitem.$$.fragment, local);
    			current = false;
    		},
    		d: function destroy(detaching) {
    			destroy_component(navitem, detaching);
    		}
    	};

    	dispatch_dev("SvelteRegisterBlock", {
    		block,
    		id: create_if_block.name,
    		type: "if",
    		source: "(10:4) {#if tab.nav && tab.title}",
    		ctx
    	});

    	return block;
    }

    // (9:3) {#each $pages as tab (tab.position)}
    function create_each_block(key_1, ctx) {
    	let first;
    	let if_block_anchor;
    	let current;
    	let if_block = /*tab*/ ctx[3].nav && /*tab*/ ctx[3].title && create_if_block(ctx);

    	const block = {
    		key: key_1,
    		first: null,
    		c: function create() {
    			first = empty();
    			if (if_block) if_block.c();
    			if_block_anchor = empty();
    			this.first = first;
    		},
    		m: function mount(target, anchor) {
    			insert_dev(target, first, anchor);
    			if (if_block) if_block.m(target, anchor);
    			insert_dev(target, if_block_anchor, anchor);
    			current = true;
    		},
    		p: function update(new_ctx, dirty) {
    			ctx = new_ctx;

    			if (/*tab*/ ctx[3].nav && /*tab*/ ctx[3].title) {
    				if (if_block) {
    					if_block.p(ctx, dirty);

    					if (dirty & /*$pages*/ 1) {
    						transition_in(if_block, 1);
    					}
    				} else {
    					if_block = create_if_block(ctx);
    					if_block.c();
    					transition_in(if_block, 1);
    					if_block.m(if_block_anchor.parentNode, if_block_anchor);
    				}
    			} else if (if_block) {
    				group_outros();

    				transition_out(if_block, 1, 1, () => {
    					if_block = null;
    				});

    				check_outros();
    			}
    		},
    		i: function intro(local) {
    			if (current) return;
    			transition_in(if_block);
    			current = true;
    		},
    		o: function outro(local) {
    			transition_out(if_block);
    			current = false;
    		},
    		d: function destroy(detaching) {
    			if (detaching) detach_dev(first);
    			if (if_block) if_block.d(detaching);
    			if (detaching) detach_dev(if_block_anchor);
    		}
    	};

    	dispatch_dev("SvelteRegisterBlock", {
    		block,
    		id: create_each_block.name,
    		type: "each",
    		source: "(9:3) {#each $pages as tab (tab.position)}",
    		ctx
    	});

    	return block;
    }

    function create_fragment$3(ctx) {
    	let div1;
    	let div0;
    	let ul;
    	let each_blocks = [];
    	let each_1_lookup = new Map();
    	let t;
    	let current;
    	let each_value = /*$pages*/ ctx[0];
    	validate_each_argument(each_value);
    	const get_key = ctx => /*tab*/ ctx[3].position;
    	validate_each_keys(ctx, each_value, get_each_context, get_key);

    	for (let i = 0; i < each_value.length; i += 1) {
    		let child_ctx = get_each_context(ctx, each_value, i);
    		let key = get_key(child_ctx);
    		each_1_lookup.set(key, each_blocks[i] = create_each_block(key, child_ctx));
    	}

    	const default_slot_template = /*#slots*/ ctx[2].default;
    	const default_slot = create_slot(default_slot_template, ctx, /*$$scope*/ ctx[1], null);

    	const block = {
    		c: function create() {
    			div1 = element("div");
    			div0 = element("div");
    			ul = element("ul");

    			for (let i = 0; i < each_blocks.length; i += 1) {
    				each_blocks[i].c();
    			}

    			t = space();
    			if (default_slot) default_slot.c();
    			attr_dev(ul, "class", "nav");
    			add_location(ul, file$2, 7, 2, 139);
    			attr_dev(div0, "class", "items");
    			add_location(div0, file$2, 6, 1, 117);
    			attr_dev(div1, "class", "nav");
    			add_location(div1, file$2, 5, 0, 98);
    		},
    		l: function claim(nodes) {
    			throw new Error("options.hydrate only works if the component was compiled with the `hydratable: true` option");
    		},
    		m: function mount(target, anchor) {
    			insert_dev(target, div1, anchor);
    			append_dev(div1, div0);
    			append_dev(div0, ul);

    			for (let i = 0; i < each_blocks.length; i += 1) {
    				each_blocks[i].m(ul, null);
    			}

    			append_dev(div0, t);

    			if (default_slot) {
    				default_slot.m(div0, null);
    			}

    			current = true;
    		},
    		p: function update(ctx, [dirty]) {
    			if (dirty & /*$pages*/ 1) {
    				each_value = /*$pages*/ ctx[0];
    				validate_each_argument(each_value);
    				group_outros();
    				validate_each_keys(ctx, each_value, get_each_context, get_key);
    				each_blocks = update_keyed_each(each_blocks, dirty, get_key, 1, ctx, each_value, each_1_lookup, ul, outro_and_destroy_block, create_each_block, null, get_each_context);
    				check_outros();
    			}

    			if (default_slot) {
    				if (default_slot.p && (!current || dirty & /*$$scope*/ 2)) {
    					update_slot_base(
    						default_slot,
    						default_slot_template,
    						ctx,
    						/*$$scope*/ ctx[1],
    						!current
    						? get_all_dirty_from_scope(/*$$scope*/ ctx[1])
    						: get_slot_changes(default_slot_template, /*$$scope*/ ctx[1], dirty, null),
    						null
    					);
    				}
    			}
    		},
    		i: function intro(local) {
    			if (current) return;

    			for (let i = 0; i < each_value.length; i += 1) {
    				transition_in(each_blocks[i]);
    			}

    			transition_in(default_slot, local);
    			current = true;
    		},
    		o: function outro(local) {
    			for (let i = 0; i < each_blocks.length; i += 1) {
    				transition_out(each_blocks[i]);
    			}

    			transition_out(default_slot, local);
    			current = false;
    		},
    		d: function destroy(detaching) {
    			if (detaching) detach_dev(div1);

    			for (let i = 0; i < each_blocks.length; i += 1) {
    				each_blocks[i].d();
    			}

    			if (default_slot) default_slot.d(detaching);
    		}
    	};

    	dispatch_dev("SvelteRegisterBlock", {
    		block,
    		id: create_fragment$3.name,
    		type: "component",
    		source: "",
    		ctx
    	});

    	return block;
    }

    function instance$3($$self, $$props, $$invalidate) {
    	let $pages;
    	validate_store(pages, 'pages');
    	component_subscribe($$self, pages, $$value => $$invalidate(0, $pages = $$value));
    	let { $$slots: slots = {}, $$scope } = $$props;
    	validate_slots('Nav', slots, ['default']);
    	const writable_props = [];

    	Object.keys($$props).forEach(key => {
    		if (!~writable_props.indexOf(key) && key.slice(0, 2) !== '$$' && key !== 'slot') console.warn(`<Nav> was created with unknown prop '${key}'`);
    	});

    	$$self.$$set = $$props => {
    		if ('$$scope' in $$props) $$invalidate(1, $$scope = $$props.$$scope);
    	};

    	$$self.$capture_state = () => ({ pages, NavItem, $pages });
    	return [$pages, $$scope, slots];
    }

    class Nav extends SvelteComponentDev {
    	constructor(options) {
    		super(options);
    		init(this, options, instance$3, create_fragment$3, safe_not_equal, {});

    		dispatch_dev("SvelteRegisterComponent", {
    			component: this,
    			tagName: "Nav",
    			options,
    			id: create_fragment$3.name
    		});
    	}
    }

    /* src/amazon-s3-and-cloudfront/ui/components/Pages.svelte generated by Svelte v3.49.0 */
    const file$1 = "src/amazon-s3-and-cloudfront/ui/components/Pages.svelte";

    function create_fragment$2(ctx) {
    	let switch_instance;
    	let t0;
    	let div;
    	let router;
    	let t1;
    	let current;
    	var switch_value = /*nav*/ ctx[0];

    	function switch_props(ctx) {
    		return { $$inline: true };
    	}

    	if (switch_value) {
    		switch_instance = new switch_value(switch_props());
    	}

    	router = new Router({
    			props: { routes: /*$routes*/ ctx[1] },
    			$$inline: true
    		});

    	router.$on("routeEvent", /*handleRouteEvent*/ ctx[3]);
    	const default_slot_template = /*#slots*/ ctx[5].default;
    	const default_slot = create_slot(default_slot_template, ctx, /*$$scope*/ ctx[4], null);

    	const block = {
    		c: function create() {
    			if (switch_instance) create_component(switch_instance.$$.fragment);
    			t0 = space();
    			div = element("div");
    			create_component(router.$$.fragment);
    			t1 = space();
    			if (default_slot) default_slot.c();
    			attr_dev(div, "class", "wpome-wrapper " + /*classes*/ ctx[2]);
    			add_location(div, file$1, 32, 0, 754);
    		},
    		l: function claim(nodes) {
    			throw new Error("options.hydrate only works if the component was compiled with the `hydratable: true` option");
    		},
    		m: function mount(target, anchor) {
    			if (switch_instance) {
    				mount_component(switch_instance, target, anchor);
    			}

    			insert_dev(target, t0, anchor);
    			insert_dev(target, div, anchor);
    			mount_component(router, div, null);
    			append_dev(div, t1);

    			if (default_slot) {
    				default_slot.m(div, null);
    			}

    			current = true;
    		},
    		p: function update(ctx, [dirty]) {
    			if (switch_value !== (switch_value = /*nav*/ ctx[0])) {
    				if (switch_instance) {
    					group_outros();
    					const old_component = switch_instance;

    					transition_out(old_component.$$.fragment, 1, 0, () => {
    						destroy_component(old_component, 1);
    					});

    					check_outros();
    				}

    				if (switch_value) {
    					switch_instance = new switch_value(switch_props());
    					create_component(switch_instance.$$.fragment);
    					transition_in(switch_instance.$$.fragment, 1);
    					mount_component(switch_instance, t0.parentNode, t0);
    				} else {
    					switch_instance = null;
    				}
    			}

    			const router_changes = {};
    			if (dirty & /*$routes*/ 2) router_changes.routes = /*$routes*/ ctx[1];
    			router.$set(router_changes);

    			if (default_slot) {
    				if (default_slot.p && (!current || dirty & /*$$scope*/ 16)) {
    					update_slot_base(
    						default_slot,
    						default_slot_template,
    						ctx,
    						/*$$scope*/ ctx[4],
    						!current
    						? get_all_dirty_from_scope(/*$$scope*/ ctx[4])
    						: get_slot_changes(default_slot_template, /*$$scope*/ ctx[4], dirty, null),
    						null
    					);
    				}
    			}
    		},
    		i: function intro(local) {
    			if (current) return;
    			if (switch_instance) transition_in(switch_instance.$$.fragment, local);
    			transition_in(router.$$.fragment, local);
    			transition_in(default_slot, local);
    			current = true;
    		},
    		o: function outro(local) {
    			if (switch_instance) transition_out(switch_instance.$$.fragment, local);
    			transition_out(router.$$.fragment, local);
    			transition_out(default_slot, local);
    			current = false;
    		},
    		d: function destroy(detaching) {
    			if (switch_instance) destroy_component(switch_instance, detaching);
    			if (detaching) detach_dev(t0);
    			if (detaching) detach_dev(div);
    			destroy_component(router);
    			if (default_slot) default_slot.d(detaching);
    		}
    	};

    	dispatch_dev("SvelteRegisterBlock", {
    		block,
    		id: create_fragment$2.name,
    		type: "component",
    		source: "",
    		ctx
    	});

    	return block;
    }

    function instance$2($$self, $$props, $$invalidate) {
    	let $routes;
    	validate_store(routes, 'routes');
    	component_subscribe($$self, routes, $$value => $$invalidate(1, $routes = $$value));
    	let { $$slots: slots = {}, $$scope } = $$props;
    	validate_slots('Pages', slots, ['default']);
    	let { nav = Nav } = $$props;
    	const classes = $$props.class ? $$props.class : "";

    	/**
     * Handles events published by the router.
     *
     * This handler gives pages a chance to put their hand up and
     * provide a new route to be navigated to in response
     * to some event.
     * e.g. settings saved resulting in a question being asked.
     *
     * @param {Object} event
     */
    	function handleRouteEvent(event) {
    		const route = pages.handleRouteEvent(event.detail);

    		if (route) {
    			push(route);
    		}
    	}

    	$$self.$$set = $$new_props => {
    		$$invalidate(6, $$props = assign(assign({}, $$props), exclude_internal_props($$new_props)));
    		if ('nav' in $$new_props) $$invalidate(0, nav = $$new_props.nav);
    		if ('$$scope' in $$new_props) $$invalidate(4, $$scope = $$new_props.$$scope);
    	};

    	$$self.$capture_state = () => ({
    		Router,
    		push,
    		pages,
    		routes,
    		Nav,
    		nav,
    		classes,
    		handleRouteEvent,
    		$routes
    	});

    	$$self.$inject_state = $$new_props => {
    		$$invalidate(6, $$props = assign(assign({}, $$props), $$new_props));
    		if ('nav' in $$props) $$invalidate(0, nav = $$new_props.nav);
    	};

    	if ($$props && "$$inject" in $$props) {
    		$$self.$inject_state($$props.$$inject);
    	}

    	$$props = exclude_internal_props($$props);
    	return [nav, $routes, classes, handleRouteEvent, $$scope, slots];
    }

    class Pages extends SvelteComponentDev {
    	constructor(options) {
    		super(options);
    		init(this, options, instance$2, create_fragment$2, safe_not_equal, { nav: 0 });

    		dispatch_dev("SvelteRegisterComponent", {
    			component: this,
    			tagName: "Pages",
    			options,
    			id: create_fragment$2.name
    		});
    	}

    	get nav() {
    		throw new Error("<Pages>: Props cannot be read directly from the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
    	}

    	set nav(value) {
    		throw new Error("<Pages>: Props cannot be set directly on the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
    	}
    }

    /* src/amazon-s3-and-cloudfront/ui/lite/Sidebar.svelte generated by Svelte v3.49.0 */
    const file = "src/amazon-s3-and-cloudfront/ui/lite/Sidebar.svelte";

    function create_fragment$1(ctx) {
    	let div3;
    	let a0;
    	let a0_href_value;
    	let t0;
    	let div0;
    	let h1;
    	let t2;
    	let h20;
    	let t4;
    	let ul0;
    	let li0;
    	let t6;
    	let li1;
    	let t8;
    	let li2;
    	let t10;
    	let li3;
    	let t12;
    	let li4;
    	let t14;
    	let li5;
    	let t16;
    	let div1;
    	let h21;
    	let t18;
    	let a1;
    	let t19;
    	let a1_href_value;
    	let t20;
    	let p;
    	let t22;
    	let div2;
    	let h4;
    	let t24;
    	let ul1;
    	let li6;
    	let a2;
    	let img;
    	let img_src_value;
    	let t25;
    	let span;
    	let a2_href_value;

    	const block = {
    		c: function create() {
    			div3 = element("div");
    			a0 = element("a");
    			t0 = space();
    			div0 = element("div");
    			h1 = element("h1");
    			h1.textContent = "Upgrade";
    			t2 = space();
    			h20 = element("h2");
    			h20.textContent = "Gain access to more features when you upgrade to WP Offload Media";
    			t4 = space();
    			ul0 = element("ul");
    			li0 = element("li");
    			li0.textContent = "Email support";
    			t6 = space();
    			li1 = element("li");
    			li1.textContent = "Offload existing media items";
    			t8 = space();
    			li2 = element("li");
    			li2.textContent = "Manage offloaded files in WordPress";
    			t10 = space();
    			li3 = element("li");
    			li3.textContent = "Serve assets like JS, CSS, and fonts from CloudFront or another CDN";
    			t12 = space();
    			li4 = element("li");
    			li4.textContent = "Deliver private media via CloudFront";
    			t14 = space();
    			li5 = element("li");
    			li5.textContent = "Offload media from popular plugins like WooCommerce, Easy Digital Downloads, BuddyBoss, and more";
    			t16 = space();
    			div1 = element("div");
    			h21 = element("h2");
    			h21.textContent = "Get up to 40% off your first year of WP Offload Media!";
    			t18 = space();
    			a1 = element("a");
    			t19 = text("Get the discount");
    			t20 = space();
    			p = element("p");
    			p.textContent = "* Discount applied automatically.";
    			t22 = space();
    			div2 = element("div");
    			h4 = element("h4");
    			h4.textContent = "Created and maintained by";
    			t24 = space();
    			ul1 = element("ul");
    			li6 = element("li");
    			a2 = element("a");
    			img = element("img");
    			t25 = space();
    			span = element("span");
    			span.textContent = "Delicious Brains";
    			attr_dev(a0, "class", "as3cf-banner");
    			attr_dev(a0, "href", a0_href_value = /*$urls*/ ctx[0].sidebar_plugin);
    			add_location(a0, file, 5, 1, 90);
    			add_location(h1, file, 8, 2, 188);
    			add_location(h20, file, 9, 2, 207);
    			add_location(li0, file, 11, 3, 292);
    			add_location(li1, file, 12, 3, 318);
    			add_location(li2, file, 13, 3, 359);
    			add_location(li3, file, 14, 3, 407);
    			add_location(li4, file, 15, 3, 487);
    			add_location(li5, file, 16, 3, 536);
    			add_location(ul0, file, 10, 2, 284);
    			attr_dev(div0, "class", "as3cf-upgrade-details");
    			add_location(div0, file, 7, 1, 150);
    			add_location(h21, file, 20, 2, 685);
    			attr_dev(a1, "href", a1_href_value = /*$urls*/ ctx[0].sidebar_discount);
    			attr_dev(a1, "class", "button btn-lg btn-primary");
    			add_location(a1, file, 21, 2, 751);
    			attr_dev(p, "class", "discount-applied");
    			add_location(p, file, 22, 2, 841);
    			attr_dev(div1, "class", "subscribe");
    			add_location(div1, file, 19, 1, 659);
    			add_location(h4, file, 25, 2, 940);
    			if (!src_url_equal(img.src, img_src_value = /*$urls*/ ctx[0].assets + "img/sidebar/logo-dbi.svg")) attr_dev(img, "src", img_src_value);
    			attr_dev(img, "alt", "Delicious Brains Inc. Logo");
    			add_location(img, file, 29, 5, 1028);
    			add_location(span, file, 30, 5, 1120);
    			attr_dev(a2, "href", a2_href_value = /*$urls*/ ctx[0].sidebar_dbi);
    			add_location(a2, file, 28, 4, 994);
    			add_location(li6, file, 27, 3, 985);
    			add_location(ul1, file, 26, 2, 977);
    			attr_dev(div2, "class", "credits");
    			add_location(div2, file, 24, 1, 916);
    			attr_dev(div3, "class", "as3cf-sidebar lite");
    			add_location(div3, file, 4, 0, 56);
    		},
    		l: function claim(nodes) {
    			throw new Error("options.hydrate only works if the component was compiled with the `hydratable: true` option");
    		},
    		m: function mount(target, anchor) {
    			insert_dev(target, div3, anchor);
    			append_dev(div3, a0);
    			append_dev(div3, t0);
    			append_dev(div3, div0);
    			append_dev(div0, h1);
    			append_dev(div0, t2);
    			append_dev(div0, h20);
    			append_dev(div0, t4);
    			append_dev(div0, ul0);
    			append_dev(ul0, li0);
    			append_dev(ul0, t6);
    			append_dev(ul0, li1);
    			append_dev(ul0, t8);
    			append_dev(ul0, li2);
    			append_dev(ul0, t10);
    			append_dev(ul0, li3);
    			append_dev(ul0, t12);
    			append_dev(ul0, li4);
    			append_dev(ul0, t14);
    			append_dev(ul0, li5);
    			append_dev(div3, t16);
    			append_dev(div3, div1);
    			append_dev(div1, h21);
    			append_dev(div1, t18);
    			append_dev(div1, a1);
    			append_dev(a1, t19);
    			append_dev(div1, t20);
    			append_dev(div1, p);
    			append_dev(div3, t22);
    			append_dev(div3, div2);
    			append_dev(div2, h4);
    			append_dev(div2, t24);
    			append_dev(div2, ul1);
    			append_dev(ul1, li6);
    			append_dev(li6, a2);
    			append_dev(a2, img);
    			append_dev(a2, t25);
    			append_dev(a2, span);
    		},
    		p: function update(ctx, [dirty]) {
    			if (dirty & /*$urls*/ 1 && a0_href_value !== (a0_href_value = /*$urls*/ ctx[0].sidebar_plugin)) {
    				attr_dev(a0, "href", a0_href_value);
    			}

    			if (dirty & /*$urls*/ 1 && a1_href_value !== (a1_href_value = /*$urls*/ ctx[0].sidebar_discount)) {
    				attr_dev(a1, "href", a1_href_value);
    			}

    			if (dirty & /*$urls*/ 1 && !src_url_equal(img.src, img_src_value = /*$urls*/ ctx[0].assets + "img/sidebar/logo-dbi.svg")) {
    				attr_dev(img, "src", img_src_value);
    			}

    			if (dirty & /*$urls*/ 1 && a2_href_value !== (a2_href_value = /*$urls*/ ctx[0].sidebar_dbi)) {
    				attr_dev(a2, "href", a2_href_value);
    			}
    		},
    		i: noop,
    		o: noop,
    		d: function destroy(detaching) {
    			if (detaching) detach_dev(div3);
    		}
    	};

    	dispatch_dev("SvelteRegisterBlock", {
    		block,
    		id: create_fragment$1.name,
    		type: "component",
    		source: "",
    		ctx
    	});

    	return block;
    }

    function instance$1($$self, $$props, $$invalidate) {
    	let $urls;
    	validate_store(urls, 'urls');
    	component_subscribe($$self, urls, $$value => $$invalidate(0, $urls = $$value));
    	let { $$slots: slots = {}, $$scope } = $$props;
    	validate_slots('Sidebar', slots, []);
    	const writable_props = [];

    	Object.keys($$props).forEach(key => {
    		if (!~writable_props.indexOf(key) && key.slice(0, 2) !== '$$' && key !== 'slot') console.warn(`<Sidebar> was created with unknown prop '${key}'`);
    	});

    	$$self.$capture_state = () => ({ urls, $urls });
    	return [$urls];
    }

    class Sidebar extends SvelteComponentDev {
    	constructor(options) {
    		super(options);
    		init(this, options, instance$1, create_fragment$1, safe_not_equal, {});

    		dispatch_dev("SvelteRegisterComponent", {
    			component: this,
    			tagName: "Sidebar",
    			options,
    			id: create_fragment$1.name
    		});
    	}
    }

    /* src/amazon-s3-and-cloudfront/ui/lite/Settings.svelte generated by Svelte v3.49.0 */

    // (116:0) <Settings header={Header}>
    function create_default_slot(ctx) {
    	let pages_1;
    	let current;

    	pages_1 = new Pages({
    			props: { class: "lite-wrapper" },
    			$$inline: true
    		});

    	const block = {
    		c: function create() {
    			create_component(pages_1.$$.fragment);
    		},
    		m: function mount(target, anchor) {
    			mount_component(pages_1, target, anchor);
    			current = true;
    		},
    		p: noop,
    		i: function intro(local) {
    			if (current) return;
    			transition_in(pages_1.$$.fragment, local);
    			current = true;
    		},
    		o: function outro(local) {
    			transition_out(pages_1.$$.fragment, local);
    			current = false;
    		},
    		d: function destroy(detaching) {
    			destroy_component(pages_1, detaching);
    		}
    	};

    	dispatch_dev("SvelteRegisterBlock", {
    		block,
    		id: create_default_slot.name,
    		type: "slot",
    		source: "(116:0) <Settings header={Header}>",
    		ctx
    	});

    	return block;
    }

    function create_fragment(ctx) {
    	let settings_1;
    	let current;

    	settings_1 = new Settings({
    			props: {
    				header: Header_1,
    				$$slots: { default: [create_default_slot] },
    				$$scope: { ctx }
    			},
    			$$inline: true
    		});

    	const block = {
    		c: function create() {
    			create_component(settings_1.$$.fragment);
    		},
    		l: function claim(nodes) {
    			throw new Error("options.hydrate only works if the component was compiled with the `hydratable: true` option");
    		},
    		m: function mount(target, anchor) {
    			mount_component(settings_1, target, anchor);
    			current = true;
    		},
    		p: function update(ctx, [dirty]) {
    			const settings_1_changes = {};

    			if (dirty & /*$$scope*/ 1024) {
    				settings_1_changes.$$scope = { dirty, ctx };
    			}

    			settings_1.$set(settings_1_changes);
    		},
    		i: function intro(local) {
    			if (current) return;
    			transition_in(settings_1.$$.fragment, local);
    			current = true;
    		},
    		o: function outro(local) {
    			transition_out(settings_1.$$.fragment, local);
    			current = false;
    		},
    		d: function destroy(detaching) {
    			destroy_component(settings_1, detaching);
    		}
    	};

    	dispatch_dev("SvelteRegisterBlock", {
    		block,
    		id: create_fragment.name,
    		type: "component",
    		source: "",
    		ctx
    	});

    	return block;
    }

    function instance($$self, $$props, $$invalidate) {
    	let $strings;
    	let $current_settings;
    	let $settings;
    	let $config;
    	let $needs_access_keys;
    	let $settingsLocked;
    	let $needs_refresh;
    	let $settings_changed;
    	validate_store(strings, 'strings');
    	component_subscribe($$self, strings, $$value => $$invalidate(1, $strings = $$value));
    	validate_store(current_settings, 'current_settings');
    	component_subscribe($$self, current_settings, $$value => $$invalidate(2, $current_settings = $$value));
    	validate_store(settings, 'settings');
    	component_subscribe($$self, settings, $$value => $$invalidate(3, $settings = $$value));
    	validate_store(config, 'config');
    	component_subscribe($$self, config, $$value => $$invalidate(4, $config = $$value));
    	validate_store(needs_access_keys, 'needs_access_keys');
    	component_subscribe($$self, needs_access_keys, $$value => $$invalidate(5, $needs_access_keys = $$value));
    	validate_store(settingsLocked, 'settingsLocked');
    	component_subscribe($$self, settingsLocked, $$value => $$invalidate(6, $settingsLocked = $$value));
    	validate_store(needs_refresh, 'needs_refresh');
    	component_subscribe($$self, needs_refresh, $$value => $$invalidate(7, $needs_refresh = $$value));
    	validate_store(settings_changed, 'settings_changed');
    	component_subscribe($$self, settings_changed, $$value => $$invalidate(8, $settings_changed = $$value));
    	let { $$slots: slots = {}, $$scope } = $$props;
    	validate_slots('Settings', slots, []);
    	let { init = {} } = $$props;

    	// During initialization set config store to passed in values to avoid undefined values in components during mount.
    	// This saves having to do a lot of checking of values before use.
    	config.set(init);

    	pages.set(defaultPages);

    	// Add Lite specific pages.
    	addPages();

    	setContext('sidebar', Sidebar);

    	/**
     * Handles state update event's changes to config.
     *
     * @param {Object} config
     *
     * @return {Promise<void>}
     */
    	async function handleStateUpdate(config) {
    		if (config.upgrades.is_upgrading) {
    			set_store_value(settingsLocked, $settingsLocked = true, $settingsLocked);

    			const notification = {
    				id: "as3cf-media-settings-locked",
    				type: "warning",
    				dismissible: false,
    				only_show_on_tab: "media",
    				heading: config.upgrades.locked_notifications[config.upgrades.running_upgrade],
    				icon: "notification-locked.svg",
    				plainHeading: true
    			};

    			notifications.add(notification);

    			if ($settings_changed) {
    				settings.reset();
    			}
    		} else if ($needs_refresh) {
    			set_store_value(settingsLocked, $settingsLocked = true, $settingsLocked);

    			const notification = {
    				id: "as3cf-media-settings-locked",
    				type: "warning",
    				dismissible: false,
    				only_show_on_tab: "media",
    				heading: $strings.needs_refresh,
    				icon: "notification-locked.svg",
    				plainHeading: true
    			};

    			notifications.add(notification);
    		} else {
    			set_store_value(settingsLocked, $settingsLocked = false, $settingsLocked);
    			notifications.delete("as3cf-media-settings-locked");
    		}

    		// Show a persistent error notice if bucket can't be accessed.
    		if ($needs_access_keys) {
    			const notification = {
    				id: "as3cf-needs-access-keys",
    				type: "error",
    				dismissible: false,
    				only_show_on_tab: "media",
    				heading: $strings.needs_access_keys,
    				plainHeading: true
    			};

    			notifications.add(notification);
    		} else {
    			notifications.delete("as3cf-needs-access-keys");
    		}
    	}

    	onMount(() => {
    		// Make sure state dependent data is up-to-date.
    		handleStateUpdate($config);

    		// When state info is fetched we need some extra processing of the data.
    		postStateUpdateCallbacks.update(_callables => {
    			return [..._callables, handleStateUpdate];
    		});
    	});

    	const writable_props = ['init'];

    	Object.keys($$props).forEach(key => {
    		if (!~writable_props.indexOf(key) && key.slice(0, 2) !== '$$' && key !== 'slot') console.warn(`<Settings> was created with unknown prop '${key}'`);
    	});

    	$$self.$$set = $$props => {
    		if ('init' in $$props) $$invalidate(0, init = $$props.init);
    	};

    	$$self.$capture_state = () => ({
    		onMount,
    		setContext,
    		config,
    		current_settings,
    		needs_access_keys,
    		needs_refresh,
    		notifications,
    		postStateUpdateCallbacks,
    		settings,
    		settings_changed,
    		settings_notifications,
    		settingsLocked,
    		strings,
    		pages,
    		defaultPages,
    		addPages,
    		settingsNotifications,
    		Settings,
    		Header: Header_1,
    		Pages,
    		Sidebar,
    		init,
    		handleStateUpdate,
    		$strings,
    		$current_settings,
    		$settings,
    		$config,
    		$needs_access_keys,
    		$settingsLocked,
    		$needs_refresh,
    		$settings_changed
    	});

    	$$self.$inject_state = $$props => {
    		if ('init' in $$props) $$invalidate(0, init = $$props.init);
    	};

    	if ($$props && "$$inject" in $$props) {
    		$$self.$inject_state($$props.$$inject);
    	}

    	$$self.$$.update = () => {
    		if ($$self.$$.dirty & /*$needs_access_keys, $config*/ 48) {
    			// Catch changes to needing access credentials as soon as possible.
    			if ($needs_access_keys) {
    				handleStateUpdate($config);
    			}
    		}

    		if ($$self.$$.dirty & /*$settings, $current_settings, $strings*/ 14) {
    			// Make sure all inline notifications are in place.
    			settings_notifications.update(notices => settingsNotifications.process(notices, $settings, $current_settings, $strings));
    		}
    	};

    	return [init, $strings, $current_settings, $settings, $config, $needs_access_keys];
    }

    class Settings_1 extends SvelteComponentDev {
    	constructor(options) {
    		super(options);
    		init(this, options, instance, create_fragment, safe_not_equal, { init: 0 });

    		dispatch_dev("SvelteRegisterComponent", {
    			component: this,
    			tagName: "Settings_1",
    			options,
    			id: create_fragment.name
    		});
    	}

    	get init() {
    		throw new Error("<Settings>: Props cannot be read directly from the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
    	}

    	set init(value) {
    		throw new Error("<Settings>: Props cannot be set directly on the component instance unless compiling with 'accessors: true' or '<svelte:options accessors/>'");
    	}
    }

    return Settings_1;

}));
//# sourceMappingURL=settings.js.map
