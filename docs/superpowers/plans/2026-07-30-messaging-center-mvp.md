# PawCircle Messaging Center — Giant Implementation Plan

## Definition Of Done

Point 8 is complete for this server-rendered prototype when all 296 requirements are assigned, the point-287 MVP works through server-validated actions, post-MVP capabilities are represented by honest integration boundaries, all eight ideal scenarios have corresponding screens/states, the existing suite is executed without adding test files, responsive behavior is browser-verified, and an isolated commit is pushed.

## Phase 1 — Grounding

1. Read live `AGENTS.md`, repository status, route tree, package scripts, and local Laravel/verification skills.
2. Record the current HEAD and preserve unrelated dirty/staged work.
3. Inventory existing message controllers, presenter, state, components, and action validation.
4. Inventory shared owner/pet/event/place/profile data that can be reused.
5. Convert points `1–296` into the coverage ledger in the design specification.
6. Expand all requirement ranges and fail on missing or duplicate numbers.

## Phase 2 — Domain Architecture

7. Create a message catalog with personal, family, event, professional, organization, search, group, and request dialogs.
8. Keep accountable humans or verified organizations as senders; store pets as context only.
9. Model per-conversation privacy, role, category, members, channels, case state, and request state.
10. Model typed messages with stable identifiers, reply references, delivery states, metadata, edits, and reactions.
11. Model per-user conversation flags: unread, pinned, muted, archived, blocked, restricted, notification level.
12. Model request decisions separately from conversation content.
13. Model tasks, polls, reports, call sessions, and call history.
14. Bound session history to avoid unbounded prototype payloads.

## Phase 3 — Server Operations

15. Add a dedicated browse request for inbox, conversation, channel, panel, and history search.
16. Add a dedicated action request with allow-listed conversations, actions, types, statuses, reasons, and controls.
17. Add a message action that validates catalog membership before every mutation.
18. Implement request acceptance and decline.
19. Implement typed sending, quiet delivery, scheduling metadata, and reply context.
20. Implement reactions, message pin/bookmark, edit, local deletion, global deletion marker, and report.
21. Implement conversation pin, mute, archive, unread, block, restriction, notification level, and export request.
22. Implement poll vote and shared-task state.
23. Implement call start, join, device control state, audio fallback, reconnect, termination, and history.
24. Return every mutation to a named route with clear feedback.

## Phase 4 — Presentation

25. Build a presenter that merges catalog and session state.
26. Filter dialogs by category, request, unread, archive, and search.
27. Search message body, sender, structured metadata, and reply context.
28. Produce linked-pet, safety, media, professional, member, channel, task, and poll view models.
29. Produce explicit provider-boundary copy for realtime, uploads, transcription, translation, encryption, recording, and business queues.
30. Keep all Blade data preloaded and query-free.

## Phase 5 — Inbox And Thread

31. Build the responsive inbox with folders, search, counts, unread markers, pin/mute/block status, and empty state.
32. Build a thread header with human identity, linked pets, verification, presence policy, and icon controls.
33. Build the request gate with preview-only information and accept/decline/block.
34. Build a professional banner with case id, working hours, assignment, and non-emergency warning.
35. Build channel navigation for group, event, and lost-pet coordination.
36. Build semantic message log and contextual search result state.
37. Render text, audio, image, video, file, place, event, task, announcement, status, warning, professional, call, system, and deleted messages.
38. Build reply, reaction, pin, bookmark, delete, edit, and report controls.
39. Build composer tools for text, audio, photo, video, file, pet, place, event, and task.
40. Add quiet send, scheduled delivery field, local draft persistence, Ctrl+Enter, upload/privacy guidance, and error output.

## Phase 6 — Contextual Workflows

41. Build member and role list.
42. Build professional case summary.
43. Build group/event poll and server mutation.
44. Build family/search/professional tasks and server mutation.
45. Build shared-content gallery categories.
46. Build safety/privacy explanation and block/restrict/export controls.
47. Build delivery-boundary disclosure so unavailable providers are never implied to work.
48. Represent family medication duplicate warning and daily care digest.
49. Represent temporary event location, travel status, announcement, and archive contract.
50. Represent lost-pet sectors, sightings, temporary location, and search closure contract.

## Phase 7 — Calls

51. Build audio/video call preflight as a modal dialog.
52. Show contact, linked pet, session state, network state, and recording-off indicator.
53. Request browser microphone/camera only from explicit test controls.
54. Stop all local tracks on form submission and page departure.
55. Implement mic, camera, captions, audio-only, reconnect, join, and end actions.
56. Keep recording off and state clearly that remote WebRTC is not connected.
57. Keep emergency veterinary warning visible in the call flow.
58. Preserve text chat as the fallback when permission or network is unavailable.

## Phase 8 — Accessibility And Responsive Design

59. Use landmarks, labels, role log, role dialog, status messages, and non-color status text.
60. Keep all action targets at least 44 px where practical.
61. Provide keyboard focus styles and Ctrl+Enter send.
62. Provide caption/transcript surfaces and non-voice participation controls.
63. Respect reduced-motion preference.
64. Desktop: show inbox, thread, and context at wide widths.
65. Tablet: show inbox and thread.
66. Mobile: show inbox first and thread after explicit conversation selection.
67. Prevent long names, case ids, pet names, and metadata from overflowing.

## Phase 9 — Verification

68. Lint all changed PHP.
69. Compile Blade views.
70. Build Vite assets.
71. List named message routes.
72. HTTP-smoke inbox, request, professional, group, family, search, and details routes.
73. Exercise CSRF-backed request acceptance and verify changed UI.
74. Exercise typed send and verify message rendering.
75. Exercise reaction, pin, mute, archive, task, poll, block, report, and call flows.
76. Run the existing test suite without creating test files.
77. Record baseline failures separately from point-8 failures.
78. Browser-check desktop at 1440×1000.
79. Browser-check tablet at 900×1100.
80. Browser-check mobile at 390×844, including inbox-first and thread-back behavior.
81. Check browser console and failed network requests.
82. Re-expand the coverage ledger and confirm `296 / 296`.
83. Scan point-8 CSS and Blade for forbidden `.pc-` and `x-pet-social` namespaces.
84. Inspect owned diff for accidental unrelated changes or generated artifacts.

## Phase 10 — Publication

85. Create a fresh temporary Git index from HEAD.
86. Stage only point-8 controllers, requests, actions, services, routes, views, JS, SCSS, and docs.
87. Inspect staged names, staged diff, and staged whitespace.
88. Commit with a point-8-specific message.
89. Push the current branch to `origin`.
90. Compare local and remote commit hashes and report exact verification evidence.
