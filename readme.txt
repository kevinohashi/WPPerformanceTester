=== WPPerformanceTester ===
Contributors: kohashi
Tags: performance, admin, benchmark
Requires at least: 3.5
Tested up to: 5.0
Stable tag: 1.1
License: GPLv3
License URI: http://www.gnu.org/licenses/gpl-3.0.html

WPPerformanceTester benchmarks your server's performance through a variety of PHP, MySql and WordPress tests

== Description ==
WPPerformanceTester was written as a tool to benchmark WordPress in the [WordPress Hosting Performance Benchmarks (2015)](http://reviewsignal.com/blog/2015/07/28/wordpress-hosting-performance-benchmarks-2015/) by [Review Signal](http://reviewsignal.com). It was designed to test the server's performance by stressing PHP, MySql and running $wpdb queries. 

WPPerformanceTester performs the following tests

- Math - 100,000 math function tests
- String Manipulation - 100,000 string manipulation tests
- Loops - 1,000,000 loop iterations
- Conditionals - 1,000,000 conditional logic checks
- MySql (connect, select, version, encode) - basic mysql functions and 1,000,000 ENCODE() iterations
- \\$wpdb - 250 insert, select, update and delete operations through \\$wpdb

It also allows you to see how your server's performance stacks up against our industry benchmark. Our industry benchmark is the average of all submitted test results.

== Installation ==
Download the plugin and install it into your *wp-content/plugins* folder.

Once activated, it should appear under the **Tools** section of your *wp-admin*.

== Changelog ==
= 1.1 =

Added support for hyperdb and socket connections.

= 1.0.1 =

Updated interface to make graphs and results more clear.

= 1.0 =

* Initial release

== Notes on Performance ==

Performance can be measured in a lot of ways. WPPerformanceTester was simply one component of a much larger performance benchmark. It tests a single server (or node) that it is running on. So if you're considering looking at the results from a clustered or distributed setup, it may give you limited insight into how well your whole system performs. WPPerformanceTester is about the raw speed a system has to execute code and perform database operations.

Real website performance isn't always correlated with raw speed. A seemingly slow website could have a very fast WPPerformanceTester result. There are lots of layers (namely caching) in making a WordPress website fast. A good caching layer will almost always outperform computing power. But when the caching layers are equal, raw speed can make a difference.

WPPerformanceTester is simply one tool to add to your toolkit in measuring performance. You should have a variety of others to test other facets of performance.

== Known Issues ==

If the script times out (max_execution_time limit) it will not show any results. You can solve this by increasing the max_execution_time in the php.ini. Some plugins may also cause WPPerformanceTester to run exceptionally slow and make it more likely to hit this limit. One such plugin is VersionPress. You can temporarily disable plugins that might be interfering with it as an alternative way to run it.

> **Note:**

> - It's always best to **BACKUP EVERYTHING** before running **ANY** new plugin or making changes to your WordPress install.