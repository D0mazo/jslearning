-- ============================================================
--  JSLearn Database Schema
--  Compatible with MySQL 5.6+ (InfinityFree)
-- ============================================================

CREATE DATABASE IF NOT EXISTS jslearn CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE jslearn;

-- Users table
CREATE TABLE IF NOT EXISTS users (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    username    VARCHAR(40)  NOT NULL UNIQUE,
    email       VARCHAR(120) NOT NULL UNIQUE,
    password    VARCHAR(255) NOT NULL,
    created_at  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    last_login  DATETIME     NULL,
    xp          INT UNSIGNED NOT NULL DEFAULT 0,
    rank_title  VARCHAR(40)  NOT NULL DEFAULT 'Beginner'
) ENGINE=InnoDB;

-- Exercises table
CREATE TABLE IF NOT EXISTS exercises (
    id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    slug         VARCHAR(80)  NOT NULL UNIQUE,
    title        VARCHAR(120) NOT NULL,
    description  TEXT         NOT NULL,
    difficulty   TINYINT      NOT NULL COMMENT '1=easy 2=medium 3=hard 4=expert',
    topic        VARCHAR(60)  NOT NULL,
    xp_reward    SMALLINT     NOT NULL DEFAULT 10,
    sort_order   SMALLINT     NOT NULL DEFAULT 0,
    hint         TEXT         NULL,
    expected_output TEXT      NULL COMMENT 'Used for display/reference only'
) ENGINE=InnoDB;

-- User progress table
CREATE TABLE IF NOT EXISTS user_progress (
    id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id      INT UNSIGNED NOT NULL,
    exercise_id  INT UNSIGNED NOT NULL,
    status       ENUM('attempted','solved') NOT NULL DEFAULT 'attempted',
    attempts     SMALLINT     NOT NULL DEFAULT 1,
    solved_at    DATETIME     NULL,
    UNIQUE KEY uq_user_exercise (user_id, exercise_id),
    FOREIGN KEY (user_id)     REFERENCES users(id)     ON DELETE CASCADE,
    FOREIGN KEY (exercise_id) REFERENCES exercises(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ============================================================
--  SEED: 60 JavaScript exercises  (difficulty 1–4)
-- ============================================================
INSERT INTO exercises (slug, title, description, difficulty, topic, xp_reward, sort_order, hint, expected_output) VALUES

-- ── VARIABLES & TYPES (difficulty 1) ──────────────────────────────────────────
('var-string-length',
 'String Length',
 'Write a function <code>strLen(s)</code> that returns the number of characters in a string.\n\n<b>Example:</b>\n<pre>strLen("hello")  // 5\nstrLen("")       // 0</pre>',
 1, 'Variables & Types', 5, 10,
 'Strings have a built-in <code>.length</code> property.',
 '5'),

('var-typeof',
 'Type Detective',
 'Write a function <code>typeOf(x)</code> that returns the type of <code>x</code> as a lowercase string.\n\n<b>Example:</b>\n<pre>typeOf(42)      // "number"\ntypeOf("hi")   // "string"\ntypeOf(true)   // "boolean"</pre>',
 1, 'Variables & Types', 5, 11,
 'The <code>typeof</code> operator returns a string.',
 '"number"'),

('var-swap',
 'Swap Two Variables',
 'Write a function <code>swap(a, b)</code> that returns an array <code>[b, a]</code> — the two values swapped.\n\n<b>Example:</b>\n<pre>swap(1, 2)   // [2, 1]\nswap("x","y") // ["y","x"]</pre>',
 1, 'Variables & Types', 5, 12,
 'Just return a new array with the arguments in reverse order.',
 '[2, 1]'),

('var-concatenate',
 'Full Name',
 'Write a function <code>fullName(first, last)</code> that returns the full name with a space between.\n\n<b>Example:</b>\n<pre>fullName("Ada", "Lovelace") // "Ada Lovelace"</pre>',
 1, 'Variables & Types', 5, 13,
 'Use the <code>+</code> operator or a template literal.',
 '"Ada Lovelace"'),

('var-celsius',
 'Celsius to Fahrenheit',
 'Write a function <code>toFahrenheit(c)</code> that converts Celsius to Fahrenheit.\n\nFormula: <code>F = C × 9/5 + 32</code>\n\n<b>Example:</b>\n<pre>toFahrenheit(0)   // 32\ntoFahrenheit(100) // 212</pre>',
 1, 'Variables & Types', 5, 14,
 'Follow the formula exactly.',
 '32'),

-- ── CONDITIONALS (difficulty 1) ───────────────────────────────────────────────
('cond-even-odd',
 'Even or Odd',
 'Write a function <code>evenOrOdd(n)</code> that returns <code>"even"</code> or <code>"odd"</code>.\n\n<b>Example:</b>\n<pre>evenOrOdd(4)  // "even"\nevenOrOdd(7)  // "odd"</pre>',
 1, 'Conditionals', 5, 20,
 'Use the modulo operator <code>%</code>.',
 '"even"'),

('cond-max',
 'Maximum of Two',
 'Write a function <code>max(a, b)</code> that returns the larger of two numbers.\n\n<b>Example:</b>\n<pre>max(3, 7)  // 7\nmax(5, 2)  // 5</pre>',
 1, 'Conditionals', 5, 21,
 'Use an <code>if</code> statement or the ternary operator.',
 '7'),

('cond-fizzbuzz-single',
 'FizzBuzz: Single Number',
 'Write a function <code>fizzBuzzOne(n)</code> that returns <code>"Fizz"</code> if divisible by 3, <code>"Buzz"</code> if by 5, <code>"FizzBuzz"</code> if by both, otherwise the number itself.\n\n<b>Example:</b>\n<pre>fizzBuzzOne(15)  // "FizzBuzz"\nfizzBuzzOne(9)   // "Fizz"\nfizzBuzzOne(7)   // 7</pre>',
 1, 'Conditionals', 5, 22,
 'Check the "both" case first.',
 '"FizzBuzz"'),

('cond-grade',
 'Letter Grade',
 'Write a function <code>grade(score)</code> that returns a letter grade:\n<ul><li>90–100 → "A"</li><li>80–89 → "B"</li><li>70–79 → "C"</li><li>60–69 → "D"</li><li>below 60 → "F"</li></ul>\n\n<b>Example:</b>\n<pre>grade(95) // "A"\ngrade(72) // "C"</pre>',
 1, 'Conditionals', 5, 23,
 'A chain of <code>else if</code> works well here.',
 '"A"'),

('cond-leap-year',
 'Leap Year',
 'Write a function <code>isLeapYear(year)</code> that returns <code>true</code> if the year is a leap year, <code>false</code> otherwise.\n\nRules: divisible by 4, EXCEPT centuries unless also divisible by 400.\n\n<b>Example:</b>\n<pre>isLeapYear(2000) // true\nisLeapYear(1900) // false\nisLeapYear(2024) // true</pre>',
 2, 'Conditionals', 10, 24,
 'Combine conditions with <code>&&</code> and <code>||</code>.',
 'true'),

-- ── LOOPS (difficulty 1–2) ────────────────────────────────────────────────────
('loop-sum',
 'Sum 1 to N',
 'Write a function <code>sumTo(n)</code> that returns the sum of all integers from 1 to n.\n\n<b>Example:</b>\n<pre>sumTo(5)  // 15  (1+2+3+4+5)\nsumTo(1)  // 1</pre>',
 1, 'Loops', 5, 30,
 'Use a <code>for</code> loop and a running total.',
 '15'),

('loop-fizzbuzz-full',
 'FizzBuzz Array',
 'Write a function <code>fizzBuzz(n)</code> that returns an array of FizzBuzz values from 1 to n.\n\n<b>Example:</b>\n<pre>fizzBuzz(5)  // [1, 2, "Fizz", 4, "Buzz"]</pre>',
 1, 'Loops', 5, 31,
 'Loop from 1 to n and push each result into an array.',
 '[1, 2, "Fizz", 4, "Buzz"]'),

('loop-factorial',
 'Factorial',
 'Write a function <code>factorial(n)</code> that returns n! (n factorial).\n\n<b>Example:</b>\n<pre>factorial(5)  // 120\nfactorial(0)  // 1</pre>',
 1, 'Loops', 5, 32,
 '0! is defined as 1. Multiply all integers from 1 to n.',
 '120'),

('loop-count-vowels',
 'Count Vowels',
 'Write a function <code>countVowels(s)</code> that returns the number of vowels (a e i o u) in a string (case-insensitive).\n\n<b>Example:</b>\n<pre>countVowels("Hello World")  // 3</pre>',
 1, 'Loops', 5, 33,
 'Loop through each character and check if it is in "aeiou".',
 '3'),

('loop-reverse-string',
 'Reverse a String',
 'Write a function <code>reverseStr(s)</code> that returns the string reversed.\n\n<b>Example:</b>\n<pre>reverseStr("abcd")  // "dcba"</pre>',
 1, 'Loops', 5, 34,
 'You can split into an array, reverse, then join.',
 '"dcba"'),

('loop-palindrome',
 'Palindrome Check',
 'Write a function <code>isPalindrome(s)</code> that returns <code>true</code> if the string reads the same forwards and backwards (ignore case).\n\n<b>Example:</b>\n<pre>isPalindrome("racecar")  // true\nisPalindrome("hello")   // false</pre>',
 2, 'Loops', 10, 35,
 'Compare the string to its reversed version.',
 'true'),

('loop-multiplication-table',
 'Multiplication Table',
 'Write a function <code>multiTable(n)</code> that returns an array of n elements where each element at index i is <code>(i+1) * n</code>.\n\n<b>Example:</b>\n<pre>multiTable(3)  // [3, 6, 9, 12, 15, 16, 21, 24, 27, 30]</pre>\n<i>Wait — that should be 10 elements: 3×1 through 3×10.</i>',
 2, 'Loops', 10, 36,
 'Use a loop from 1 to 10.',
 '[3, 6, 9, 12, 15, 18, 21, 24, 27, 30]'),

-- ── ARRAYS (difficulty 1–3) ───────────────────────────────────────────────────
('arr-sum',
 'Array Sum',
 'Write a function <code>arraySum(arr)</code> that returns the sum of all numbers in an array.\n\n<b>Example:</b>\n<pre>arraySum([1, 2, 3, 4])  // 10\narraySum([])            // 0</pre>',
 1, 'Arrays', 5, 40,
 'Use <code>reduce</code> or a <code>for</code> loop.',
 '10'),

('arr-max',
 'Array Maximum',
 'Write a function <code>arrayMax(arr)</code> that returns the largest number in an array.\n\n<b>Example:</b>\n<pre>arrayMax([3, 1, 4, 1, 5, 9])  // 9</pre>',
 1, 'Arrays', 5, 41,
 '<code>Math.max(...arr)</code> is a clean one-liner.',
 '9'),

('arr-unique',
 'Remove Duplicates',
 'Write a function <code>unique(arr)</code> that returns a new array with duplicate values removed.\n\n<b>Example:</b>\n<pre>unique([1, 2, 2, 3, 3, 3])  // [1, 2, 3]</pre>',
 2, 'Arrays', 10, 42,
 '<code>new Set(arr)</code> removes duplicates — spread it back into an array.',
 '[1, 2, 3]'),

('arr-flatten',
 'Flatten Nested Array',
 'Write a function <code>flatten(arr)</code> that flattens one level of nesting.\n\n<b>Example:</b>\n<pre>flatten([[1,2],[3,4],[5]])  // [1,2,3,4,5]</pre>',
 2, 'Arrays', 10, 43,
 'Try <code>arr.flat()</code> or use <code>concat</code> with the spread operator.',
 '[1, 2, 3, 4, 5]'),

('arr-chunk',
 'Chunk Array',
 'Write a function <code>chunk(arr, size)</code> that splits an array into groups of <code>size</code>.\n\n<b>Example:</b>\n<pre>chunk([1,2,3,4,5], 2)  // [[1,2],[3,4],[5]]</pre>',
 2, 'Arrays', 10, 44,
 'Use a <code>while</code> loop and <code>slice</code>.',
 '[[1,2],[3,4],[5]]'),

('arr-rotate',
 'Rotate Array',
 'Write a function <code>rotate(arr, k)</code> that rotates the array to the right by <code>k</code> positions.\n\n<b>Example:</b>\n<pre>rotate([1,2,3,4,5], 2)  // [4,5,1,2,3]</pre>',
 3, 'Arrays', 20, 45,
 'Use <code>slice</code> to split the array at position <code>arr.length - k</code>.',
 '[4, 5, 1, 2, 3]'),

('arr-intersection',
 'Array Intersection',
 'Write a function <code>intersection(a, b)</code> that returns elements present in both arrays.\n\n<b>Example:</b>\n<pre>intersection([1,2,3,4],[2,4,6])  // [2,4]</pre>',
 2, 'Arrays', 10, 46,
 'Use <code>filter</code> combined with <code>includes</code>.',
 '[2, 4]'),

('arr-zip',
 'Zip Two Arrays',
 'Write a function <code>zip(a, b)</code> that pairs elements from two arrays by index.\n\n<b>Example:</b>\n<pre>zip([1,2,3],["a","b","c"])  // [[1,"a"],[2,"b"],[3,"c"]]</pre>',
 2, 'Arrays', 10, 47,
 'Loop by index up to the shorter array length.',
 '[[1,"a"],[2,"b"],[3,"c"]]'),

-- ── OBJECTS (difficulty 2–3) ──────────────────────────────────────────────────
('obj-keys-count',
 'Count Object Keys',
 'Write a function <code>keyCount(obj)</code> that returns the number of own properties in an object.\n\n<b>Example:</b>\n<pre>keyCount({a:1, b:2, c:3})  // 3</pre>',
 2, 'Objects', 10, 50,
 '<code>Object.keys()</code> returns an array of property names.',
 '3'),

('obj-invert',
 'Invert Object',
 'Write a function <code>invert(obj)</code> that swaps keys and values.\n\n<b>Example:</b>\n<pre>invert({a:"x", b:"y"})  // {x:"a", y:"b"}</pre>',
 2, 'Objects', 10, 51,
 'Loop over <code>Object.entries()</code>.',
 '{x:"a", y:"b"}'),

('obj-merge',
 'Deep Count',
 'Write a function <code>countValues(obj)</code> that returns the total number of values in a flat object that are numbers greater than zero.\n\n<b>Example:</b>\n<pre>countValues({a:1, b:-1, c:5, d:"x"})  // 2</pre>',
 2, 'Objects', 10, 52,
 'Use <code>Object.values()</code> and filter.',
 '2'),

('obj-group-by',
 'Group By',
 'Write a function <code>groupBy(arr, key)</code> that groups an array of objects by a property.\n\n<b>Example:</b>\n<pre>groupBy([{t:"a",v:1},{t:"b",v:2},{t:"a",v:3}], "t")\n// {a:[{t:"a",v:1},{t:"a",v:3}], b:[{t:"b",v:2}]}</pre>',
 3, 'Objects', 20, 53,
 'Use <code>reduce</code> and build up the result object.',
 '{a:[...], b:[...]}'),

('obj-pick',
 'Pick Properties',
 'Write a function <code>pick(obj, keys)</code> that returns a new object with only the specified keys.\n\n<b>Example:</b>\n<pre>pick({a:1,b:2,c:3}, ["a","c"])  // {a:1, c:3}</pre>',
 2, 'Objects', 10, 54,
 'Loop over the keys array and build a new object.',
 '{a:1, c:3}'),

-- ── FUNCTIONS & CLOSURES (difficulty 2–3) ─────────────────────────────────────
('fn-memoize',
 'Memoize a Function',
 'Write a function <code>memoize(fn)</code> that returns a cached version of <code>fn</code>. Repeated calls with the same argument return the cached result.\n\n<b>Example:</b>\n<pre>const memoSqrt = memoize(Math.sqrt);\nmemoSqrt(16)  // 4  (computed)\nmemoSqrt(16)  // 4  (from cache)</pre>',
 3, 'Functions & Closures', 20, 60,
 'Use a closure over a plain object as the cache. Key on the first argument.',
 '4'),

('fn-once',
 'Call Once',
 'Write a function <code>once(fn)</code> that returns a wrapper which calls <code>fn</code> at most once. Subsequent calls return the first result.\n\n<b>Example:</b>\n<pre>const initOnce = once(() => 42);\ninitOnce()  // 42\ninitOnce()  // 42  (not called again)</pre>',
 3, 'Functions & Closures', 20, 61,
 'Store a <code>called</code> flag and a <code>result</code> in a closure.',
 '42'),

('fn-curry',
 'Curry a Function',
 'Write a function <code>curry(fn)</code> that curries a two-argument function.\n\n<b>Example:</b>\n<pre>const add = curry((a, b) => a + b);\nadd(3)(4)  // 7</pre>',
 3, 'Functions & Closures', 20, 62,
 'Return a function that takes the first argument and returns another function for the second.',
 '7'),

('fn-compose',
 'Function Composition',
 'Write a function <code>compose(f, g)</code> that returns a new function that applies g first, then f.\n\n<b>Example:</b>\n<pre>const double = x => x * 2;\nconst inc    = x => x + 1;\ncompose(double, inc)(3)  // 8  (inc(3)=4, double(4)=8)</pre>',
 3, 'Functions & Closures', 20, 63,
 'Return <code>x => f(g(x))</code>.',
 '8'),

-- ── STRINGS (difficulty 1–2) ──────────────────────────────────────────────────
('str-capitalize',
 'Capitalize Words',
 'Write a function <code>capitalizeWords(s)</code> that capitalizes the first letter of every word.\n\n<b>Example:</b>\n<pre>capitalizeWords("hello world")  // "Hello World"</pre>',
 1, 'Strings', 5, 70,
 'Split on spaces, capitalize each word, then join.',
 '"Hello World"'),

('str-word-count',
 'Word Count',
 'Write a function <code>wordCount(s)</code> that returns an object mapping each word to its frequency (case-insensitive).\n\n<b>Example:</b>\n<pre>wordCount("the cat sat on the mat")\n// {the:2, cat:1, sat:1, on:1, mat:1}</pre>',
 2, 'Strings', 10, 71,
 'Split the string, lowercase everything, then count with an object.',
 '{the:2, cat:1, ...}'),

('str-anagram',
 'Anagram Check',
 'Write a function <code>isAnagram(a, b)</code> that returns <code>true</code> if the two strings are anagrams of each other (ignore case and spaces).\n\n<b>Example:</b>\n<pre>isAnagram("listen", "silent")  // true\nisAnagram("hello",  "world")   // false</pre>',
 2, 'Strings', 10, 72,
 'Sort both strings alphabetically and compare them.',
 'true'),

('str-truncate',
 'Truncate String',
 'Write a function <code>truncate(s, maxLen)</code> that shortens the string to <code>maxLen</code> characters and appends <code>"..."</code> if it was truncated.\n\n<b>Example:</b>\n<pre>truncate("Hello World", 7)  // "Hello W..."</pre>',
 1, 'Strings', 5, 73,
 'Check length first; if over, return the slice plus "...".',
 '"Hello W..."'),

('str-camel-to-snake',
 'camelCase → snake_case',
 'Write a function <code>toSnakeCase(s)</code> that converts camelCase to snake_case.\n\n<b>Example:</b>\n<pre>toSnakeCase("helloWorld")     // "hello_world"\ntoSnakeCase("myVarName")      // "my_var_name"</pre>',
 2, 'Strings', 10, 74,
 'Use a regular expression to find uppercase letters and replace them.',
 '"hello_world"'),

-- ── RECURSION (difficulty 2–3) ────────────────────────────────────────────────
('rec-fibonacci',
 'Fibonacci',
 'Write a function <code>fib(n)</code> that returns the nth Fibonacci number (0-indexed).\n\n<b>Example:</b>\n<pre>fib(0)  // 0\nfib(1)  // 1\nfib(6)  // 8</pre>',
 2, 'Recursion', 10, 80,
 'Base cases: fib(0)=0, fib(1)=1. Then recurse.',
 '8'),

('rec-power',
 'Power Function',
 'Write a recursive function <code>power(base, exp)</code> that returns base raised to exp (non-negative integer).\n\n<b>Example:</b>\n<pre>power(2, 10)  // 1024\npower(3, 0)   // 1</pre>',
 2, 'Recursion', 10, 81,
 'Base case: exp === 0 returns 1. Otherwise return base * power(base, exp-1).',
 '1024'),

('rec-deep-flatten',
 'Deep Flatten',
 'Write a recursive function <code>deepFlatten(arr)</code> that flattens an arbitrarily nested array.\n\n<b>Example:</b>\n<pre>deepFlatten([1,[2,[3,[4]]],5])  // [1,2,3,4,5]</pre>',
 3, 'Recursion', 20, 82,
 'Check if each element <code>Array.isArray</code>; if so, recurse.',
 '[1, 2, 3, 4, 5]'),

('rec-tree-sum',
 'Binary Tree Sum',
 'Write a function <code>treeSum(node)</code> that sums all values in a binary tree. Nodes have shape <code>{val, left, right}</code> where left/right may be null.\n\n<b>Example:</b>\n<pre>treeSum({val:1, left:{val:2,left:null,right:null}, right:{val:3,left:null,right:null}})\n// 6</pre>',
 3, 'Recursion', 20, 83,
 'Base case: null node returns 0.',
 '6'),

-- ── HIGHER-ORDER FUNCTIONS (difficulty 2–3) ───────────────────────────────────
('hof-map',
 'Implement map()',
 'Write your own <code>myMap(arr, fn)</code> — do not use the built-in <code>.map()</code>.\n\n<b>Example:</b>\n<pre>myMap([1,2,3], x => x * 2)  // [2,4,6]</pre>',
 2, 'Higher-Order Functions', 10, 90,
 'Use a <code>for</code> loop and push transformed values.',
 '[2, 4, 6]'),

('hof-filter',
 'Implement filter()',
 'Write your own <code>myFilter(arr, fn)</code> — do not use the built-in <code>.filter()</code>.\n\n<b>Example:</b>\n<pre>myFilter([1,2,3,4,5], x => x % 2 === 0)  // [2,4]</pre>',
 2, 'Higher-Order Functions', 10, 91,
 'Only push elements for which fn returns true.',
 '[2, 4]'),

('hof-reduce',
 'Implement reduce()',
 'Write your own <code>myReduce(arr, fn, initial)</code> — do not use the built-in <code>.reduce()</code>.\n\n<b>Example:</b>\n<pre>myReduce([1,2,3,4], (acc,x) => acc+x, 0)  // 10</pre>',
 2, 'Higher-Order Functions', 10, 92,
 'Start with the initial value and apply fn at each step.',
 '10'),

('hof-pipe',
 'Pipe Functions',
 'Write a function <code>pipe(...fns)</code> that applies an array of functions left-to-right.\n\n<b>Example:</b>\n<pre>const process = pipe(x=>x+1, x=>x*2, x=>x-3);\nprocess(5)  // 9  ((5+1)*2)-3</pre>',
 3, 'Higher-Order Functions', 20, 93,
 'Use <code>reduce</code> on the functions array.',
 '9'),

-- ── ASYNC & PROMISES (difficulty 2–4) ────────────────────────────────────────
('async-delay',
 'Delay Promise',
 'Write a function <code>delay(ms)</code> that returns a Promise which resolves after <code>ms</code> milliseconds.\n\n<b>Example:</b>\n<pre>await delay(500); // waits 500ms</pre>',
 2, 'Async & Promises', 10, 100,
 'Wrap <code>setTimeout</code> in a <code>new Promise</code>.',
 'resolves after ms'),

('async-fetch-json',
 'Fetch with Error Handling',
 'Write an async function <code>fetchJSON(url)</code> that fetches a URL and returns the parsed JSON. If the response is not ok, throw an Error with the status code.\n\n<b>Example:</b>\n<pre>const data = await fetchJSON("https://api.example.com/data");</pre>',
 2, 'Async & Promises', 10, 101,
 'Check <code>response.ok</code> before calling <code>response.json()</code>.',
 'parsed JSON object'),

('async-parallel',
 'Run Promises in Parallel',
 'Write a function <code>fetchAll(urls)</code> that fetches all URLs in parallel and returns an array of parsed JSON results.\n\n<b>Example:</b>\n<pre>const results = await fetchAll(["url1","url2","url3"]);</pre>',
 3, 'Async & Promises', 20, 102,
 'Map the urls to fetch calls and use <code>Promise.all</code>.',
 'array of results'),

('async-retry',
 'Retry on Failure',
 'Write an async function <code>retry(fn, times)</code> that calls <code>fn()</code> and retries up to <code>times</code> times if it rejects.\n\n<b>Example:</b>\n<pre>await retry(unstableFn, 3);</pre>',
 4, 'Async & Promises', 40, 103,
 'Use a loop; only throw after all attempts are exhausted.',
 'resolves or throws after N attempts'),

-- ── ERROR HANDLING (difficulty 2) ────────────────────────────────────────────
('err-safe-parse',
 'Safe JSON Parse',
 'Write a function <code>safeParseJSON(str)</code> that returns the parsed object on success, or <code>null</code> on failure — never throws.\n\n<b>Example:</b>\n<pre>safeParseJSON(\'{"a":1}\')  // {a:1}\nsafeParseJSON("oops")     // null</pre>',
 2, 'Error Handling', 10, 110,
 'Wrap <code>JSON.parse</code> in a try/catch.',
 '{a:1}'),

-- ── DOM / WEB APIs (difficulty 2–3) ──────────────────────────────────────────
('dom-toggle-class',
 'Toggle Class',
 'Write a function <code>toggleClass(el, className)</code> that adds the class if the element does not have it, removes it if it does. Do not use <code>classList.toggle</code>.\n\n<b>Example:</b>\n<pre>toggleClass(document.body, "dark");</pre>',
 2, 'DOM & Web APIs', 10, 120,
 'Use <code>classList.contains</code> then <code>add</code>/<code>remove</code>.',
 'class toggled'),

('dom-debounce',
 'Debounce',
 'Write a function <code>debounce(fn, wait)</code> that returns a debounced version of <code>fn</code> — it only fires after <code>wait</code> ms of inactivity.\n\n<b>Example:</b>\n<pre>const dSearch = debounce(search, 300);\ndSearch();dSearch();dSearch(); // only fires once</pre>',
 3, 'DOM & Web APIs', 20, 121,
 'Use <code>clearTimeout</code> / <code>setTimeout</code> inside the wrapper.',
 'fires once after 300ms'),

('dom-throttle',
 'Throttle',
 'Write a function <code>throttle(fn, limit)</code> that returns a throttled function — it fires at most once per <code>limit</code> ms.\n\n<b>Example:</b>\n<pre>const tScroll = throttle(onScroll, 200);</pre>',
 3, 'DOM & Web APIs', 20, 122,
 'Track the last call timestamp.',
 'fires at most once per limit ms'),

-- ── ALGORITHMS (difficulty 3–4) ───────────────────────────────────────────────
('algo-binary-search',
 'Binary Search',
 'Write a function <code>binarySearch(arr, target)</code> that returns the index of <code>target</code> in a sorted array, or <code>-1</code> if not found.\n\n<b>Example:</b>\n<pre>binarySearch([1,3,5,7,9,11], 7)  // 3\nbinarySearch([1,3,5,7,9,11], 4)  // -1</pre>',
 3, 'Algorithms', 20, 130,
 'Maintain low and high pointers; compare the midpoint each iteration.',
 '3'),

('algo-bubble-sort',
 'Bubble Sort',
 'Write a function <code>bubbleSort(arr)</code> that sorts an array of numbers in ascending order using the bubble sort algorithm.\n\n<b>Example:</b>\n<pre>bubbleSort([5,3,8,1,2])  // [1,2,3,5,8]</pre>',
 3, 'Algorithms', 20, 131,
 'Repeatedly swap adjacent elements that are in the wrong order.',
 '[1, 2, 3, 5, 8]'),

('algo-anagram-groups',
 'Group Anagrams',
 'Write a function <code>groupAnagrams(words)</code> that groups words that are anagrams of each other.\n\n<b>Example:</b>\n<pre>groupAnagrams(["eat","tea","tan","ate","nat","bat"])\n// [["eat","tea","ate"],["tan","nat"],["bat"]]</pre>',
 3, 'Algorithms', 20, 132,
 'Use a sorted version of each word as a map key.',
 '[["eat","tea","ate"],...]'),

('algo-longest-substring',
 'Longest Non-Repeating Substring',
 'Write a function <code>lengthOfLongestSubstring(s)</code> that returns the length of the longest substring without repeating characters.\n\n<b>Example:</b>\n<pre>lengthOfLongestSubstring("abcabcbb")  // 3  ("abc")\nlengthOfLongestSubstring("pwwkew")    // 3  ("wke")</pre>',
 4, 'Algorithms', 40, 133,
 'Use the sliding window technique with a Set.',
 '3'),

('algo-two-sum',
 'Two Sum',
 'Write a function <code>twoSum(nums, target)</code> that returns the indices of two numbers that add up to <code>target</code>.\n\n<b>Example:</b>\n<pre>twoSum([2,7,11,15], 9)  // [0,1]</pre>',
 3, 'Algorithms', 20, 134,
 'Use a hash map to store complements. One-pass O(n) solution is possible.',
 '[0, 1]'),

('algo-lru-cache',
 'LRU Cache',
 'Implement an LRU (Least Recently Used) cache class with a given capacity.\n\n<b>Methods:</b>\n<ul><li><code>get(key)</code> — return value or -1</li><li><code>put(key, value)</code> — insert, evict LRU if over capacity</li></ul>\n\n<b>Example:</b>\n<pre>const lru = new LRUCache(2);\nlru.put(1,1); lru.put(2,2);\nlru.get(1);   // 1\nlru.put(3,3); // evicts key 2\nlru.get(2);   // -1</pre>',
 4, 'Algorithms', 40, 135,
 'A Map in JavaScript maintains insertion order — use it as an ordered hash.',
 '1, then -1');
