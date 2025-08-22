from odoo import models, fields

class VetAnimal(models.Model):
    _name = 'vet.animal'
    _description = 'Patient Animal'

    name = fields.Char(string='Name', required=True)
    owner_id = fields.Many2one('vet.owner', string='Owner', required=True)
    species = fields.Selection([('placeholder', 'Placeholder')], string='Species')
    breed = fields.Char(string='Breed')
    date_of_birth = fields.Date(string='Date of Birth')
    vaccination_status = fields.Selection([('placeholder', 'Placeholder')], string='Vaccination Status')
    neutered_status = fields.Selection([('placeholder', 'Placeholder')], string='Neutered Status')
