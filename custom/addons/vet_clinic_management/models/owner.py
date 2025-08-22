from odoo import models, fields

class VetOwner(models.Model):
    _name = 'vet.owner'
    _description = 'Veterinary Clinic Owner'

    name = fields.Char(string='Name', required=True)
    phone = fields.Char(string='Phone')
    email = fields.Char(string='Email')
    address = fields.Text(string='Address')
    animal_ids = fields.One2many('vet.animal', 'owner_id', string='Animals')
