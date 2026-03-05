Quick-Guides
======

.. toctree::
   :maxdepth: 2
   :caption: How the core of the platform works
   
   general
   dashboard-modules
   user-meta-service
   
Docs & Reports
--------------
.. toctree::
   :maxdepth: 3
   :caption: How can the documentation and reporting system be tested locally?
   
   docs-report/index
   
Introduction
============

One of the requirements of the application is to allow both beginner or more advanced developers to work on this project simultaneously.

To achieve this, **OOP modularity** must be maintained while keeping the codebase simple and approachable.

In some cases, code that could be optimized or refactored is intentionally left as is to enhance readability for newcomers. 

For example, in JavaScript, the AJAX calls currently use repetitive jQuery code. While these could be merged into a single function, keeping them separate helps new developers quickly grasp core concepts without added complexity.