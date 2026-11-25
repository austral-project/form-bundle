/*
 * This file is part of the Austral Form Bundle package.
 *
 * (c) Austral <support@austral.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

import { twig } from '@sensiolabs/storybook-symfony-webpack5';
export default {
  viewMode: 'canvas',
  render: (args) => ({
    tags: ['autodocs'],
    template: twig`
            <twig:Austral:Form:Row tag="div" name="{{ name }}" typeField="{{ type }}" labelPosition="{{ labelPosition }}" reverse="{{ reverse }}" disabled="{{ disabled }}">
              ${args.labelPosition !== "external" ? '<twig:block name="label"><twig:Austral:Form:Label tag="label" entitled="{{ label }}" id="{{ id }}"/></twig:block>' : ''}
              ${args.labelPosition === "external" ? '<twig:block name="label_external"><twig:Austral:Form:Label tag="label" entitled="{{ label }}" id="{{ id }}"/></twig:block>' : ''}
              <twig:block name="field">
                <twig:Austral:Form:Input 
                  type="{{ type }}"
                  id="{{ id }}"
                  name="{{ name }}"
                  required="{{ required }}"
                  disabled="{{ disabled }}"
                  value="{{ value }}"
                />
              </twig:block>
              ${args.after ? '<twig:block name="after">'+args.after+'</twig:block>' : ''}
              ${args.before ? '<twig:block name="before">'+args.before+'</twig:block>' : ''}
            </twig:Austral:Form:Row>
        `
  })
};

const argTypes = {
  type: {
    name: 'Type',
    description: 'Type',
    options: ['text', 'number'],
    control: {type: 'select', required: true},
    table: {
      type: {summary: 'string'},
      defaultValue: {summary: 'null'}
    },
    value: "text",
  },
  id: {
    name: 'ID',
    description: 'Field ID',
    control: {type: 'text', required: true},
    table: {
      type: {summary: 'string'},
      defaultValue: {summary: 'null'}
    },
  },
  name: {
    name: 'Name',
    description: 'Field name',
    control: {type: 'text', required: true},
    table: {
      type: {summary: 'string'},
      defaultValue: {summary: 'null'}
    },
  },
  label: {
    name: 'Label',
    description: 'Field label',
    control: {type: 'text'},
    table: {
      type: {summary: 'string'},
      defaultValue: {summary: 'null'}
    }
  },
  labelPosition: {
    name: 'Label Position',
    description: 'Position for the label',
    options: ['default', "animate", 'external'],
    control: {type: 'select', required: true},
    table: {
      type: {summary: 'string'},
      defaultValue: {summary: 'default'}
    }
  },
  reverse: {
    name: 'Reverse',
    description: 'Reverse label and input',
    control: {type: 'boolean'},
    table: {
      type: {summary: 'boolean'},
      defaultValue: {summary: 'false'}
    },
  },
  required: {
    name: 'Required',
    description: 'Value is required',
    control: {type: 'boolean'},
    table: {
      type: {summary: 'boolean'},
      defaultValue: {summary: 'false'}
    },
  },
  disabled: {
    name: 'Disabled',
    description: 'Field is disabled',
    control: {type: 'boolean'},
    table: {
      type: {summary: 'boolean'},
      defaultValue: {summary: 'false'}
    },
  },
  value: {
    name: 'Value',
    description: 'Field value',
    control: {type: 'text'},
    table: {
      type: {summary: 'string'},
      defaultValue: {summary: 'null'}
    }
  },
  before: {
    name: 'Before',
    description: 'Before field content',
    control: {type: 'text'},
    table: {
      type: {summary: 'string'},
      defaultValue: {summary: 'null'}
    }
  },
  after: {
    name: 'After',
    description: 'After field content',
    control: {type: 'text'},
    table: {
      type: {summary: 'string'},
      defaultValue: {summary: 'null'}
    }
  },
};
export const Default = {
  tags:     ['autodocs'],
  argTypes: argTypes,
  args: {
    type: "text",
    id: "name",
    name: "name",
    labelPosition: "default",
    label: "Label",
    reverse: false,
    disabled: false,
    required: false,
    value: "",
    before: "",
    after: "",
  },
};

export const WithAfter = {
  tags:     ['autodocs'],
  argTypes: argTypes,
  args: {
    type: "text",
    id: "name",
    name: "name",
    labelPosition: "default",
    label: "Label",
    reverse: false,
    disabled: false,
    required: false,
    value: "",
    before: "",
    after: "<twig:Austral:Form:Row tag=\"div\" name=\"test\" labelPosition=\"default\">\n" +
      "  <twig:block name=\"label\"><twig:Austral:Form:Label tag=\"label\" entitled=\"Test name\" id=\"test\"/></twig:block>\n" +
      "  <twig:block name=\"field\">\n" +
      "    <twig:Austral:Form:Input\n" +
      "    type=\"text\"\n" +
      "    id=\"test\"\n" +
      "    name=\"test\"\n" +
      "    value=\"\"\n" +
      "    />\n" +
      "  </twig:block>\n" +
      "</twig:Austral:Form:Row>",
  },
};