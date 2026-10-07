# Role avatars: brief for the Canva session

The ERP shows a picture next to every person, chosen by their role, whenever they have no photo of their own.
The pictures live in `public/images/roles/` and are picked by file name, so replacing a file replaces the
avatar everywhere, with no code change. The files there now are plain placeholders.

## What to make

12 square images, **512 × 512 px, PNG**, one per role. One consistent style across the whole set: friendly flat
illustration of a person (head and shoulders) with one small prop that hints at the job, on a solid or softly
shaded coloured background. Keep the face area centred, because the app crops the picture to a circle. Avoid
text. Make them readable at 32 px.

| File name | Role | Suggested look |
| --- | --- | --- |
| `admin.png` | Admin | Dark navy background, shield or key |
| `inventory-manager.png` | Inventory Manager | Blue, clipboard and stacked boxes |
| `warehouse-staff.png` | Warehouse Staff | Teal, hi-vis vest and hand trolley |
| `purchase-manager.png` | Purchase Manager | Violet, shopping basket or purchase order |
| `sales-manager.png` | Sales Manager | Orange, price tag or handshake |
| `accountant.png` | Accountant | Green, calculator and ledger |
| `hr-manager.png` | HR Manager | Pink, speech bubble or team of figures |
| `employee.png` | Employee | Cyan, laptop and lanyard |
| `project-manager.png` | Project Manager | Indigo, flag or kanban board |
| `manufacturing-manager.png` | Manufacturing Manager | Amber, hard hat and cog |
| `maintenance-technician.png` | Maintenance Technician | Red, wrench and overalls |
| `viewer.png` | Viewer | Slate grey, glasses or eye |

## Prompt to give Claude in the local session (with Canva connected)

> Using Canva, design the 12 role avatars described in `docs/ROLE_AVATARS_CANVA.md`. Use one consistent flat
> illustration style. Export each as a 512 × 512 PNG and save it into `public/images/roles/` with exactly the
> file names in the table, replacing the placeholders. Then run `php artisan optimize:clear` and open the
> Users page to check the pictures show up.

## How the app picks a picture

1. The person's own photo, if they uploaded one.
2. The picture of their most senior role (order: Admin, Inventory Manager, Purchase Manager, Sales Manager,
   Accountant, Manufacturing Manager, HR Manager, Project Manager, Warehouse Staff, Maintenance Technician,
   Employee, Viewer).
3. Filament's coloured initials.

An administrator can also upload a different picture for any role (including roles they create themselves) in
**Settings → Roles → edit the role → Role picture**. An uploaded picture wins over the file in this folder.
