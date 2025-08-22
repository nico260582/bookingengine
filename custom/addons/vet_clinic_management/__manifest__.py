{
    'name': "Veterinary Clinic Management",
    'summary': "Comprehensive management system for veterinary clinic operations.",
    'description': """
        Manages patient records, consultations, inventory, billing,
        and specialized services like grooming and hospitalization.
        Features a cost-saving two-tier user access model.
    """,
    'author': "Zoomania Vet Services",
    'website': "https://www.yourcompany.com",
    'category': 'Industries',
    'version': '18.0.1.0.0',
    'depends': ['base', 'mail', 'product', 'account'],
    'data': [
        'security/ir.model.access.csv',
        'security/vet_clinic_security.xml',
        'views/owner_views.xml',
        'views/animal_views.xml',
        'views/vet_menus.xml',
    ],
    'installable': True,
    'application': True,
    'auto_install': False,
}
