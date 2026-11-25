# Entités workflow et flux Messenger

## Entités workflow

Les workflows sont définis dans `apps/config/packages/workflow.yaml`. Marking store : méthode `state` sur l'entité (propriété `state`). Audit trail activé (debug uniquement pour la plupart).

Entités concernées (trait `Labstag\Entity\Traits\WorkflowTrait`) : `Chapter`, `Edito`, `Story`, `Memo`, `Post`, `Page`, `User`.

### Workflow commun de contenu (chapter/edito/story/memo/post/page)

Places : `start` → `draft` → `rereading` → `published` | `rejected`.

Transitions :
- `submit` : start → draft
- `reread` : draft → rereading
- `correct`/`fix` : rereading → draft
- `publish` : rereading → published
- `reject` : rereading → rejected

`Chapter` utilise `correct`, les autres `fix`.

### Workflow utilisateur

Places : `start` → `unverifed` → `valid` ↔ `lostpassword`, `valid` ↔ `disable`.

Transitions : `submit` (start→unverifed), `approval` (unverifed→valid), `passwordlost` (valid→lostpassword), `changepassword` (lostpassword→valid), `deactivate` (valid→disable), `activate` (disable→valid).

### Écouteurs

`Labstag\Event\Workflow\*Workflow` (AsTransitionListener) : Memo, Story, User (et probablement Chapter/Edito/Post/Page).

## Flux Messenger

Config : `apps/config/packages/messenger.yaml`.

Transports : `async` (DSN `MESSENGER_TRANSPORT_DSN`, retry 3, delay 5s×2, max 60s), `failed` (doctrine failed queue), `sync`.

Messages : `apps/src/Message/*Message.php`. Routing actuellement commenté (pas de routing explicite → bus sync par défaut). Failure transport `failed` actif.

Messages principaux : import/méta (MetaMessage, MetaAllMessage), cinema (Movie*, Serie*, Season*, Episode*, Saga*, Person*, Company*, PageCinemaMessage), jeux (Game*, AddGameMessage, SearchGameMessage), utilisateur (UserEmailMessage, BanIpMessage, NotificationMessage), fichiers (FileDeleteMessage, DeleteOldFileMessage), stories (StoryMessage, StoryAllMessage), cache (ClearCacheMessage), saga (SagaMessage, SagaAllMessage).

## Notes

- Routing à configurer pour basculer les messages lourds vers `async`.
- Voir `MessengerService`/handlers dans `apps/src/MessageHandler`.