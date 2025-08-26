# P0 — Foundation & Vertical Slice (ship something end-to-end)

## 1)gi Project skeleton & layering (Baseline)

* [ ] Create bundle skeletons:

  * `Aurora\Domain\*` (pure PHP, no Symfony deps): `Content`, `Media`, `Workspace`, `Security`
  * `Aurora\Application\*` (use cases / orchestrators): `Content`, `Media`, `Workspace`
  * `Aurora\Infrastructure\*` (adapters): `PersistenceDoctrine`, `Filesystem`, `Auth`
  * `Aurora\Interface\Http` (controllers, HTTP DTOs), `Aurora\Interface\Cli`
* [ ] Composer package structure (monorepo-friendly): separate namespaces, minimal cross deps
* [ ] Common: `Aurora\Shared\Kernel` (domain events, result types, value object base, exceptions)

## 2) Content Repository v1 (CRv1) — minimal viable

* [ ] Domain model (entities/value objects):

  * `NodeId`, `Node`, `NodePath`, `NodeType`, `Property`, `DimensionSet`, `WorkspaceId`
* [ ] Aggregates & invariants (no infra): create/move/remove node, set props, resolve by path
* [ ] Application use cases:

  * `CreateNode`, `SetProperty`, `MoveNode`, `DeleteNode`, `GetNodeByPath`
* [ ] Infra (Doctrine ORM 3):

  * Mappings for Node graph (adjacency list to start), Type table, basic indices
  * Repositories: `NodeRepository`, `NodeTypeRepository`
* [ ] HTTP Interface:

  * Minimal admin endpoints for CRUD over nodes + simple tree view JSON
* [ ] Acceptance tests: end-to-end for create→read→update→move nodes

## 3) NodeType system v1 (inspired by Neos, simplified)

* [ ] YAML/JSON-based NodeType definitions (Infra loader) → `Domain\NodeType`
* [ ] Validation & inheritance (merge with constraints & default props)
* [ ] App service: `ResolveEffectiveNodeType` (composition & caching)
* [ ] CLI: `node-types:validate`, `node-types:list`

## 4) Rendering pipeline v1 (Fusion-lite)

* [ ] Domain: `ViewModel`/`RendererContract` abstractions (no framework coupling)
* [ ] App: `RenderNode` use case (resolve node → pick renderer → render)
* [ ] Infra: Pluggable renderers (Twig first), simple slot/area concept
* [ ] HTTP: `GET /render/{path}` for public rendering

## 5) Workspaces & publishing v1 (draft → live)

* [ ] Domain: `Workspace` aggregate, `ChangeSet`, `PublishPolicy`
* [ ] App: `CreateWorkspace`, `CommitChanges`, `PublishWorkspace`
* [ ] Infra: table for staged changes (copy-on-write per workspace)
* [ ] HTTP: endpoints to create draft, show changes, publish to live

# P1 — Robustness, Editing UX, and Media

## 6) Content editing ergonomics

* [ ] Patch semantics (JSON Patch) for property updates
* [ ] Locking/optimistic concurrency on nodes
* [ ] Simple audit log (Domain events persisted)

## 7) Media/Asset basic module

* [ ] Domain: `AssetId`, `Asset`, `Variant` (renditions), `AssetUsage`
* [ ] App: `UploadAsset`, `GenerateVariant`, `AttachAssetToNode`
* [ ] Infra: Filesystem storage + Doctrine metadata; async variant generation hook
* [ ] HTTP: upload/list/serve endpoints (with signed URLs option later)

## 8) Search (indexing abstraction)

* [ ] Domain: `IndexableContent` interface and `SearchQuery` value objects
* [ ] App: `IndexNode`, `SearchNodes`
* [ ] Infra: start with DB full-text, leave adapter seam for Meilisearch/Elastic

## 9) Permissions & roles (coarse-grained first)

* [ ] Domain: `Role`, `Permission`, `Policy` checkers
* [ ] App: `AuthorizeAction` decorator around use cases
* [ ] Infra: Symfony Security integration, basic RBAC seed

# P2 — Developer Experience & Extensibility

## 10) Extension & plugin API

* [ ] DI extension points for:

  * NodeType providers
  * Renderers
  * Property editors (admin UI contracts)
* [ ] Eventing: Domain events → Symfony Messenger bridge (optional)
* [ ] Versioned public PHP interfaces for stable extension points

## 11) Migrations & importers

* [ ] Content import/export format (JSON Lines)
* [ ] CLI: `content:export`, `content:import`
* [ ] Optional Neos bridge: importer for NodeTypes and a basic content map

## 12) Observability & operations

* [ ] Health endpoints, DB & queue checks
* [ ] Structured logging (request + domain correlation id)
* [ ] Metrics: render time, repo ops, publish ops
* [ ] Feature flags (config + env + cache)

# P3 — Advanced Capabilities

## 13) Dimensions & personalization

* [ ] Domain: `DimensionValue`, `DimensionResolutionPolicy`
* [ ] App: resolve node by dimension set; fallbacks
* [ ] Infra: indexing and cache keys with dimensions

## 14) Content workflows

* [ ] States: draft/review/approved/published
* [ ] Review assignments & notifications (app service + HTTP)
* [ ] Pluggable rules (policy objects)

## 15) Event-sourced CR (optional path)

* [ ] Replace adjacency list with event stream + projections
* [ ] Rebuild, time-travel queries, better audit

---

## Bundle Map (Clean Architecture → Symfony)

**Domain (pure PHP):**

* `Aurora\Domain\Content`
* `Aurora\Domain\NodeType`
* `Aurora\Domain\Workspace`
* `Aurora\Domain\Security`
* `Aurora\Domain\Media`
* `Aurora\Shared`

**Application (use cases):**

* `Aurora\Application\Content`
* `Aurora\Application\NodeType`
* `Aurora\Application\Workspace`
* `Aurora\Application\Media`

**Infrastructure (adapters):**

* `Aurora\Infrastructure\PersistenceDoctrine`
* `Aurora\Infrastructure\Filesystem`
* `Aurora\Infrastructure\Auth`
* `Aurora\Infrastructure\Search` (later)

**Interface (delivery):**

* `Aurora\Interface\Http` (controllers, request/response DTOs)
* `Aurora\Interface\Cli` (commands)

> Rule of thumb: Interface → Application → Domain (downward deps only). Infrastructure depends on Application/Domain; nothing depends on Infrastructure concretions—use ports.

---

## Non-functional gates (apply per feature)

* [ ] ✅ Unit + app tests for use cases
* [ ] ✅ Contract tests for repositories & adapters
* [ ] ✅ Performance baseline (render + repo ops)
* [ ] ✅ Security checks (authorization on mutating use cases)
* [ ] ✅ Documentation snippet (ADR or mini-RFC per module)

---

## First week target (suggested)

* [ ] P0.1 skeleton + composer + CI
* [ ] P0.2 CRv1 minimal (create/get by path)
* [ ] P0.4 rendering Twig path
* [ ] Tiny demo: create a Page node, set a title, render `/render/`.

If you want, I can spin this into GitHub issues (one per checkbox) with acceptance criteria and a dependency graph.
