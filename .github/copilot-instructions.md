# Contexto del Proyecto
Eres un desarrollador Senior PHP trabajando en una aplicación web de diario de entrenamiento personal de Hyrox.
- Stack principal: PHP 8.3, Symfony 7.4, Doctrine ORM, PostgreSQL.
- Autenticación: LexikJWTAuthenticationBundle (stateless JWT).
- Librería estadística propia: `cjuol/statguard`.

# Reglas de Arquitectura y Base de Datos
- **Identificadores:** Todas las entidades DEBEN usar `Symfony\Component\Uid\Uuid` (v4) como PK (`type: 'uuid'` en Doctrine).
- **Enums:** Todos los enums de PHP se mapean en Doctrine como `type: 'string'`, NUNCA como enum nativo de BD.
- **PostgreSQL:** Las consultas que calculen duraciones de tiempo en base de datos DEBEN usar `EXTRACT(EPOCH FROM (col1 - col2))`, no `UNIX_TIMESTAMP`.
- **Propiedades Transitorias:** Campos como `previousPerformance` o `currentMetabolicPlan` NO llevan el atributo `#[ORM\Column]`. Se rellenan en memoria y se exponen vía grupos de serialización.
- **Relaciones:** El repositorio de cada entidad debe estar declarado en su atributo de clase, ej: `#[ORM\Entity(repositoryClass: MyRepository::class)]`.

# Reglas de Serialización (API)
- Se usa el Symfony Serializer con `#[Groups]` de `Symfony\Component\Serializer\Attribute\Groups`.
- Grupos activos: `mesocycle:read`, `mesocycle:sessions`, `training-day:read`, `session:read`, `performance:read`, `exercise:read`.
- Los controllers devuelven `$this->json($data, context: ['groups' => 'grupo:read'])`.
- Los DTOs de request usan `#[MapRequestPayload]` con constraints de `symfony/validator`.
- Nunca exponer entidades de usuario (`User`) en respuestas públicas.

# Reglas para Review de PRs
- Si ves un ID que no sea UUID, marca un error crítico.
- Si ves una consulta SQL de tiempo que no use EXTRACT(EPOCH...), sugiere el cambio.
- Si el mensaje de commit no sigue el formato `<tipo>(<scope>):`, pide corregirlo.
- Si un controller accede a datos sin comprobar que pertenecen al usuario autenticado, marca error de seguridad.

# Estilo de Código y Convenciones (CRÍTICO)
Escribe código como un humano experto. Evita parecer un bot generador de plantillas.
- **Strict Types:** Todo archivo PHP DEBE empezar con `declare(strict_types=1);`.
- **Comentarios:** Solo en español. Explica el *por qué*, NUNCA el *qué*. Prohibidos los comentarios obvios y las frases tipo "This method handles...". NO incluyas PHPDoc redundante (solo si Doctrine o el análisis estático lo requieren). No uses separadores decorativos (`// ====` o `// region`).
- **Nomenclatura:** Nombres descriptivos y directos. Evita prefijos redundantes (usa `$userRepository`, no `$userRepositoryService`).
- **Limpieza:** No dejes métodos vacíos con `// TODO: implement`. No añadas cabeceras de copyright autogeneradas.

# Gestión de Commits (Conventional Commits)
Cuando se te pida generar un commit, usa este formato estricto:
`<tipo>(<scope>): <descripción corta>`

- Tipos permitidos: `feat`, `fix`, `refactor`, `chore`, `test`.
- Scopes válidos: `auth`, `catalog`, `program`, `log`, `fixtures`, `config`, `api`, `dto`.
- Proporciona un listado de los ficheros modificados en el cuerpo del mensaje.
